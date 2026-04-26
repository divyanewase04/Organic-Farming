<?php
declare(strict_types=1);

require_once __DIR__ . '/includes/bootstrap.php';
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/cart.php';
require_once __DIR__ . '/includes/layout.php';

requireLogin();

$pdo = getPDO();
$user = currentUser();
$summary = cartSummary($pdo);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $token = $_POST['csrf_token'] ?? null;
    $name = trim($_POST['name'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $address = trim($_POST['address'] ?? '');
    $pincode = trim($_POST['pincode'] ?? '');
    $paymentMode = $_POST['payment_mode'] ?? '';

    if (!validateCsrfToken($token)) {
        setFlash('error', 'Invalid request token.');
    } elseif ($summary['items'] === []) {
        setFlash('error', 'Cart is empty.');
    } elseif ($name === '' || $address === '' || $pincode === '') {
        setFlash('error', 'Please fill all address details.');
    } elseif (!preg_match('/^[0-9]{10}$/', $phone)) {
        setFlash('error', 'Phone number must be 10 digits.');
    } elseif (!in_array($paymentMode, ['COD', 'ONLINE'], true)) {
        setFlash('error', 'Select a payment mode.');
    } else {
        $subtotal = (float) $summary['subtotal'];
        $deliveryCharge = $subtotal >= 499 ? 0.0 : 50.0;
        $total = $subtotal + $deliveryCharge;
        $expectedDelivery = date('Y-m-d', strtotime('+3 days'));
        $orderCode = 'OF' . date('Ymd') . random_int(1000, 9999);

        try {
            $pdo->beginTransaction();

            $insertOrder = $pdo->prepare(
                'INSERT INTO orders
                (order_code, user_id, status, payment_mode, subtotal, delivery_charge, total_amount, customer_name, phone, address_line, pincode, expected_delivery)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)'
            );
            $insertOrder->execute([
                $orderCode,
                (int) $user['id'],
                'PLACED',
                $paymentMode,
                $subtotal,
                $deliveryCharge,
                $total,
                $name,
                $phone,
                $address,
                $pincode,
                $expectedDelivery,
            ]);

            $orderId = (int) $pdo->lastInsertId();
            $insertItem = $pdo->prepare(
                'INSERT INTO order_items (order_id, product_id, product_name, unit_price, quantity, line_total) VALUES (?, ?, ?, ?, ?, ?)'
            );
            $decreaseStock = $pdo->prepare(
                'UPDATE products SET stock_qty = stock_qty - ? WHERE id = ? AND stock_qty >= ?'
            );

            foreach ($summary['items'] as $item) {
                $insertItem->execute([
                    $orderId,
                    (int) $item['id'],
                    $item['name'],
                    (float) $item['price'],
                    (int) $item['quantity'],
                    (float) $item['line_total'],
                ]);

                $decreaseStock->execute([
                    (int) $item['quantity'],
                    (int) $item['id'],
                    (int) $item['quantity'],
                ]);

                if ($decreaseStock->rowCount() === 0) {
                    throw new RuntimeException('Stock validation failed for product ID ' . (int) $item['id']);
                }
            }

            $pdo->commit();
            cartClear();

            setFlash('success', 'Order placed successfully. Order Code: ' . $orderCode);
            header('Location: order_success.php?code=' . urlencode($orderCode));
            exit();
        } catch (Throwable $exception) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            setFlash('error', 'Order could not be placed. Please retry.');
        }
    }

    header('Location: order.php');
    exit();
}

$deliveryCharge = $summary['subtotal'] >= 499 ? 0 : 50;
$estimatedTotal = $summary['subtotal'] + $deliveryCharge;
$csrfToken = generateCsrfToken();

renderHeader('Checkout', 'order');
?>
<main class="page-wrap">
  <section class="panel">
    <h1>Checkout</h1>

    <?php if ($summary['items'] === []): ?>
    <p class="muted">Your cart is empty. Add products to continue checkout.</p>
    <a class="primary-btn link-btn" href="products.php">Browse Products</a>
    <?php else: ?>
    <div class="table-wrap">
      <table>
        <thead>
          <tr>
            <th>Product</th>
            <th>Qty</th>
            <th>Price</th>
            <th>Total</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($summary['items'] as $item): ?>
          <tr>
            <td><?= e($item['name']) ?></td>
            <td><?= (int) $item['quantity'] ?></td>
            <td>INR <?= number_format((float) $item['price'], 2) ?></td>
            <td>INR <?= number_format((float) $item['line_total'], 2) ?></td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>

    <div class="checkout-grid">
      <form class="panel" method="post">
        <input type="hidden" name="csrf_token" value="<?= e($csrfToken) ?>">
        <h2>Delivery Details</h2>
        <label for="name">Full Name</label>
        <input id="name" type="text" name="name" value="<?= e($user['name']) ?>" required>

        <label for="phone">Phone</label>
        <input id="phone" type="tel" name="phone" maxlength="10" required>

        <label for="address">Address</label>
        <textarea id="address" name="address" rows="3" required></textarea>

        <label for="pincode">Pincode</label>
        <input id="pincode" type="text" name="pincode" maxlength="6" required>

        <label for="payment_mode">Payment Mode</label>
        <select id="payment_mode" name="payment_mode" required>
          <option value="">Select</option>
          <option value="ONLINE">Online Payment</option>
          <option value="COD">Cash On Delivery</option>
        </select>

        <button class="primary-btn" type="submit">Place Order</button>
      </form>

      <aside class="panel hero-callout">
        <h2>Bill Summary</h2>
        <div class="summary-list">
          <p class="summary-item"><span>Subtotal</span><strong>INR <?= number_format((float) $summary['subtotal'], 2) ?></strong></p>
          <p class="summary-item"><span>Delivery</span><strong>INR <?= number_format((float) $deliveryCharge, 2) ?></strong></p>
          <p class="summary-item"><span>Grand Total</span><strong>INR <?= number_format((float) $estimatedTotal, 2) ?></strong></p>
        </div>
        <p class="muted">Expected delivery in 2-4 days.</p>
      </aside>
    </div>
    <?php endif; ?>
  </section>
</main>
<?php renderFooter(); ?>
