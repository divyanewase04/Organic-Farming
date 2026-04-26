<?php
declare(strict_types=1);

require_once __DIR__ . '/includes/bootstrap.php';
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/layout.php';

requireLogin();

$pdo = getPDO();
$user = currentUser();
$code = trim($_GET['code'] ?? '');

$order = null;
$items = [];

if ($code !== '') {
    $orderStatement = $pdo->prepare(
        'SELECT id, order_code, status, payment_mode, subtotal, delivery_charge, total_amount, expected_delivery, created_at
         FROM orders WHERE order_code = ? AND user_id = ? LIMIT 1'
    );
    $orderStatement->execute([$code, (int) $user['id']]);
    $order = $orderStatement->fetch();

    if ($order) {
        $itemsStatement = $pdo->prepare(
            'SELECT product_name, unit_price, quantity, line_total
             FROM order_items WHERE order_id = ? ORDER BY id ASC'
        );
        $itemsStatement->execute([(int) $order['id']]);
        $items = $itemsStatement->fetchAll();
    }
}

renderHeader('Order Success', 'order');
?>
<main class="page-wrap">
  <section class="panel">
    <?php if (!$order): ?>
    <h1>Order Not Found</h1>
    <p class="muted">We could not locate that order. Open order history to verify.</p>
    <a class="primary-btn link-btn" href="order_history.php">Open Order History</a>
    <?php else: ?>
    <h1>Order Confirmed</h1>
    <p>Your order <strong><?= e($order['order_code']) ?></strong> has been placed successfully.</p>
    <p class="muted">Expected delivery: <?= e(date('d M Y', strtotime($order['expected_delivery']))) ?></p>

    <div class="table-wrap">
      <table>
        <thead>
          <tr>
            <th>Product</th>
            <th>Qty</th>
            <th>Unit Price</th>
            <th>Total</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($items as $item): ?>
          <tr>
            <td><?= e($item['product_name']) ?></td>
            <td><?= (int) $item['quantity'] ?></td>
            <td>INR <?= number_format((float) $item['unit_price'], 2) ?></td>
            <td>INR <?= number_format((float) $item['line_total'], 2) ?></td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>

    <div class="cart-totals">
      <div class="summary-list">
        <p class="summary-item"><span>Subtotal</span><strong>INR <?= number_format((float) $order['subtotal'], 2) ?></strong></p>
        <p class="summary-item"><span>Delivery</span><strong>INR <?= number_format((float) $order['delivery_charge'], 2) ?></strong></p>
        <p class="summary-item"><span>Total Paid</span><strong>INR <?= number_format((float) $order['total_amount'], 2) ?></strong></p>
        <p class="summary-item"><span>Status</span><span class="badge"><?= e($order['status']) ?></span></p>
      </div>
    </div>

    <div class="button-row">
      <a class="secondary-btn link-btn" href="products.php">Order More</a>
      <a class="primary-btn link-btn" href="order_history.php">View Order History</a>
    </div>
    <?php endif; ?>
  </section>
</main>
<?php renderFooter(); ?>
