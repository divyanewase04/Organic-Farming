<?php
declare(strict_types=1);

require_once __DIR__ . '/includes/bootstrap.php';
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/cart.php';
require_once __DIR__ . '/includes/layout.php';

requireLogin();
$pdo = getPDO();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $token = $_POST['csrf_token'] ?? null;
    $action = $_POST['action'] ?? '';

    if (!validateCsrfToken($token)) {
        setFlash('error', 'Invalid token. Please retry.');
        header('Location: cart.php');
        exit();
    }

    if ($action === 'remove') {
        $productId = (int) ($_POST['product_id'] ?? 0);
        cartRemove($productId);
        setFlash('success', 'Item removed from cart.');
    } elseif ($action === 'clear') {
        cartClear();
        setFlash('success', 'Cart cleared.');
    }

    header('Location: cart.php');
    exit();
}

$summary = cartSummary($pdo);
$deliveryCharge = $summary['subtotal'] >= 499 ? 0 : 50;
$estimatedTotal = $summary['subtotal'] + $deliveryCharge;
$csrfToken = generateCsrfToken();

renderHeader('Cart', 'cart');
?>
<main class="page-wrap">
  <section class="panel">
    <div class="panel-header">
      <h1>Shopping Cart</h1>
      <a class="text-link" href="products.php">Continue Shopping</a>
    </div>

    <?php if ($summary['items'] === []): ?>
    <p class="muted">Your cart is empty.</p>
    <a class="primary-btn link-btn" href="products.php">Browse Products</a>
    <?php else: ?>
    <div class="table-wrap">
      <table>
        <thead>
          <tr>
            <th>Product</th>
            <th>Unit Price</th>
            <th>Qty</th>
            <th>Line Total</th>
            <th>Action</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($summary['items'] as $item): ?>
          <tr>
            <td><?= e($item['name']) ?></td>
            <td>INR <?= number_format((float) $item['price'], 2) ?></td>
            <td><?= (int) $item['quantity'] ?></td>
            <td>INR <?= number_format((float) $item['line_total'], 2) ?></td>
            <td>
              <form method="post">
                <input type="hidden" name="csrf_token" value="<?= e($csrfToken) ?>">
                <input type="hidden" name="action" value="remove">
                <input type="hidden" name="product_id" value="<?= (int) $item['id'] ?>">
                <button class="danger-btn" type="submit">Remove</button>
              </form>
            </td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>

    <div class="cart-totals">
      <div class="summary-list">
        <p class="summary-item"><span>Subtotal</span><strong>INR <?= number_format((float) $summary['subtotal'], 2) ?></strong></p>
        <p class="summary-item"><span>Delivery</span><strong>INR <?= number_format((float) $deliveryCharge, 2) ?></strong></p>
        <p class="summary-item"><span>Estimated Total</span><strong>INR <?= number_format((float) $estimatedTotal, 2) ?></strong></p>
      </div>
      <div class="button-row">
        <a class="primary-btn link-btn" href="order.php">Proceed To Checkout</a>
        <form method="post">
          <input type="hidden" name="csrf_token" value="<?= e($csrfToken) ?>">
          <input type="hidden" name="action" value="clear">
          <button class="danger-btn" type="submit">Clear Cart</button>
        </form>
      </div>
    </div>
    <?php endif; ?>
  </section>
</main>
<?php renderFooter(); ?>
