<?php
declare(strict_types=1);

require_once __DIR__ . '/includes/bootstrap.php';
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/layout.php';

requireLogin();

$pdo = getPDO();
$user = currentUser();

$statusFilter = trim($_GET['status'] ?? 'all');
$params = [(int) $user['id']];

$query = 'SELECT order_code, status, payment_mode, total_amount, expected_delivery, created_at
          FROM orders WHERE user_id = ?';

if ($statusFilter !== '' && $statusFilter !== 'all') {
    $query .= ' AND status = ?';
    $params[] = $statusFilter;
}

$query .= ' ORDER BY created_at DESC';
$statement = $pdo->prepare($query);
$statement->execute($params);
$orders = $statement->fetchAll();

renderHeader('Order History', 'history');
?>
<main class="page-wrap">
  <section class="panel">
    <div class="panel-header">
      <h1>Order History</h1>
      <a class="text-link" href="products.php">New Order</a>
    </div>

    <form method="get" class="filter-grid">
      <div>
        <label for="status">Status</label>
        <select id="status" name="status">
          <option value="all">All</option>
          <?php foreach (['PLACED', 'PROCESSING', 'SHIPPED', 'DELIVERED', 'CANCELLED'] as $status): ?>
          <option value="<?= e($status) ?>" <?= $statusFilter === $status ? 'selected' : '' ?>>
            <?= e($status) ?>
          </option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="button-row">
        <button class="secondary-btn" type="submit">Filter</button>
      </div>
    </form>

    <?php if ($orders === []): ?>
    <p class="muted">No orders found for the selected filter.</p>
    <?php else: ?>
    <div class="table-wrap">
      <table>
        <thead>
          <tr>
            <th>Order Code</th>
            <th>Status</th>
            <th>Payment</th>
            <th>Total</th>
            <th>Delivery</th>
            <th>Created</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($orders as $order): ?>
          <tr>
            <td>
              <a class="text-link" href="order_success.php?code=<?= urlencode($order['order_code']) ?>">
                <?= e($order['order_code']) ?>
              </a>
            </td>
            <td><span class="badge"><?= e($order['status']) ?></span></td>
            <td><?= e($order['payment_mode']) ?></td>
            <td>INR <?= number_format((float) $order['total_amount'], 2) ?></td>
            <td><?= e(date('d M Y', strtotime($order['expected_delivery']))) ?></td>
            <td><?= e(date('d M Y, h:i A', strtotime($order['created_at']))) ?></td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
    <?php endif; ?>
  </section>
</main>
<?php renderFooter(); ?>
