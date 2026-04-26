<?php
declare(strict_types=1);

require_once __DIR__ . '/includes/bootstrap.php';
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/layout.php';

requireLogin();

$pdo = getPDO();
$user = currentUser();

$productCount = (int) $pdo->query('SELECT COUNT(*) AS total FROM products WHERE is_active = 1')->fetch()['total'];

$ordersStatement = $pdo->prepare('SELECT COUNT(*) AS total FROM orders WHERE user_id = ?');
$ordersStatement->execute([(int) $user['id']]);
$myOrderCount = (int) $ordersStatement->fetch()['total'];

$spendStatement = $pdo->prepare(
    "SELECT COALESCE(SUM(total_amount), 0) AS total FROM orders WHERE user_id = ? AND status != 'CANCELLED'"
);
$spendStatement->execute([(int) $user['id']]);
$mySpend = (float) $spendStatement->fetch()['total'];

$recentOrdersStatement = $pdo->prepare(
    'SELECT order_code, status, total_amount, created_at FROM orders WHERE user_id = ? ORDER BY created_at DESC LIMIT 5'
);
$recentOrdersStatement->execute([(int) $user['id']]);
$recentOrders = $recentOrdersStatement->fetchAll();

renderHeader('Dashboard', 'home');
?>
<main class="page-wrap">
  <section class="hero-panel">
    <h1>Organic Farming Management Platform</h1>
    <p>Track products, place orders, and manage interactions with a MySQL-backed workflow.</p>
    <div class="metric-chips">
      <span class="metric-chip">Secure Login</span>
      <span class="metric-chip">MySQL Orders</span>
      <span class="metric-chip">Smart Assistant</span>
    </div>
    <div class="hero-actions">
      <a class="primary-btn link-btn" href="products.php">Browse Products</a>
      <a class="secondary-btn link-btn" href="importance.php">Read Organic Guide</a>
    </div>
  </section>

  <section class="stats-grid">
    <article class="stat-card">
      <h2>Available Products</h2>
      <p class="stat-value"><?= $productCount ?></p>
    </article>
    <article class="stat-card">
      <h2>My Total Orders</h2>
      <p class="stat-value"><?= $myOrderCount ?></p>
    </article>
    <article class="stat-card">
      <h2>My Lifetime Spend</h2>
      <p class="stat-value">INR <?= number_format($mySpend, 2) ?></p>
    </article>
  </section>

  <section class="panel">
    <div class="panel-header">
      <h2>Recent Orders</h2>
      <a class="text-link" href="order_history.php">View Full History</a>
    </div>
    <?php if ($recentOrders === []): ?>
    <p class="muted">No orders placed yet. Start with products and checkout.</p>
    <?php else: ?>
    <div class="table-wrap">
      <table>
        <thead>
          <tr>
            <th>Order Code</th>
            <th>Status</th>
            <th>Total</th>
            <th>Placed On</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($recentOrders as $order): ?>
          <tr>
            <td><?= e($order['order_code']) ?></td>
            <td><span class="badge"><?= e($order['status']) ?></span></td>
            <td>INR <?= number_format((float) $order['total_amount'], 2) ?></td>
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
