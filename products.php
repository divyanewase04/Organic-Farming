<?php
declare(strict_types=1);

require_once __DIR__ . '/includes/bootstrap.php';
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/cart.php';
require_once __DIR__ . '/includes/layout.php';

requireLogin();

$pdo = getPDO();

$search = trim($_GET['q'] ?? '');
$category = trim($_GET['category'] ?? 'all');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $token = $_POST['csrf_token'] ?? null;
    $productId = (int) ($_POST['product_id'] ?? 0);
    $quantity = max(1, min(20, (int) ($_POST['quantity'] ?? 1)));

    if (!validateCsrfToken($token)) {
        setFlash('error', 'Invalid request token. Please try again.');
    } else {
        $check = $pdo->prepare('SELECT id, name, stock_qty, is_active FROM products WHERE id = ?');
        $check->execute([$productId]);
        $product = $check->fetch();

        if (!$product || (int) $product['is_active'] !== 1) {
            setFlash('error', 'Product is unavailable.');
        } elseif ((int) $product['stock_qty'] < $quantity) {
            setFlash('error', 'Requested quantity is more than available stock.');
        } else {
            cartAdd($productId, $quantity);
            setFlash('success', $product['name'] . ' added to cart.');
        }
    }

    $query = http_build_query([
        'q' => $search,
        'category' => $category,
    ]);
    header('Location: products.php' . ($query ? '?' . $query : ''));
    exit();
}

$categories = $pdo->query('SELECT DISTINCT category FROM products WHERE is_active = 1 ORDER BY category ASC')->fetchAll();

$sql = 'SELECT id, sku, name, category, unit, price, stock_qty, image_url, description 
        FROM products 
        WHERE is_active = 1';
$params = [];

if ($search !== '') {
    $sql .= ' AND (name LIKE ? OR description LIKE ?)';
    $params[] = '%' . $search . '%';
    $params[] = '%' . $search . '%';
}

if ($category !== '' && $category !== 'all') {
    $sql .= ' AND category = ?';
    $params[] = $category;
}

$sql .= ' ORDER BY name ASC';
$statement = $pdo->prepare($sql);
$statement->execute($params);
$products = $statement->fetchAll();

$summary = cartSummary($pdo);
$csrfToken = generateCsrfToken();

renderHeader('Products', 'products');
?>
<main class="page-wrap">
  <section class="panel">
    <div class="panel-header">
      <h1>Product Catalog</h1>
      <a class="text-link" href="cart.php">Go To Cart (<?= cartItemCount() ?>)</a>
    </div>

    <form class="filter-grid" method="get">
      <div>
        <label for="q">Search</label>
        <input id="q" type="text" name="q" value="<?= e($search) ?>" placeholder="Find products...">
      </div>
      <div>
        <label for="category">Category</label>
        <select id="category" name="category">
          <option value="all">All Categories</option>
          <?php foreach ($categories as $categoryRow): ?>
          <?php $value = (string) $categoryRow['category']; ?>
          <option value="<?= e($value) ?>" <?= $category === $value ? 'selected' : '' ?>>
            <?= e($value) ?>
          </option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="button-row">
        <button class="secondary-btn" type="submit">Apply Filter</button>
      </div>
    </form>
  </section>

  <section class="products-layout">
    <div class="product-grid">
      <?php if ($products === []): ?>
      <article class="panel">
        <p class="muted">No products found for current filters.</p>
      </article>
      <?php endif; ?>

      <?php foreach ($products as $product): ?>
      <article class="product-card">
        <img src="<?= e($product['image_url']) ?>" alt="<?= e($product['name']) ?>">
        <div class="product-content">
          <p class="pill"><?= e($product['category']) ?></p>
          <h2><?= e($product['name']) ?></h2>
          <p><?= e($product['description']) ?></p>
          <p class="price">INR <?= number_format((float) $product['price'], 2) ?> / <?= e($product['unit']) ?></p>
          <p class="muted">Stock: <?= (int) $product['stock_qty'] ?></p>
        </div>
        <form method="post" class="cart-form">
          <input type="hidden" name="csrf_token" value="<?= e($csrfToken) ?>">
          <input type="hidden" name="product_id" value="<?= (int) $product['id'] ?>">
          <input type="number" name="quantity" min="1" max="20" value="1">
          <button class="primary-btn" type="submit">Add To Cart</button>
        </form>
      </article>
      <?php endforeach; ?>
    </div>

    <aside class="panel sticky">
      <h3>Cart Snapshot</h3>
      <p class="muted"><?= count($summary['items']) ?> items selected</p>
      <ul class="mini-list">
        <?php foreach (array_slice($summary['items'], 0, 5) as $item): ?>
        <li><?= e($item['name']) ?> x <?= (int) $item['quantity'] ?></li>
        <?php endforeach; ?>
      </ul>
      <p class="price">Subtotal: INR <?= number_format((float) $summary['subtotal'], 2) ?></p>
      <a class="primary-btn link-btn block" href="cart.php">Manage Cart</a>
    </aside>
  </section>
</main>
<?php renderFooter(); ?>
