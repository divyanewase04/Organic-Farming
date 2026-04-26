<?php
declare(strict_types=1);

require_once __DIR__ . '/bootstrap.php';
require_once __DIR__ . '/cart.php';

function isActive(string $expected, string $active): string
{
    return $expected === $active ? 'active' : '';
}

function renderHeader(string $title, string $active): void
{
    $user = currentUser();
    $flash = getFlash();
    $cartCount = cartItemCount();
    ?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= e($title) ?> | Organic Farming</title>
  <link rel="stylesheet" href="style.css">
</head>
<body>
  <header class="top-nav">
    <div class="brand">Organic Farming</div>
    <nav class="menu">
      <a class="<?= isActive('home', $active) ?>" href="index.php">Home</a>
      <a class="<?= isActive('products', $active) ?>" href="products.php">Products</a>
      <a class="<?= isActive('cart', $active) ?>" href="cart.php">Cart (<?= $cartCount ?>)</a>
      <a class="<?= isActive('order', $active) ?>" href="order.php">Order</a>
      <a class="<?= isActive('history', $active) ?>" href="order_history.php">History</a>
      <a class="<?= isActive('chatbot', $active) ?>" href="chatbot.php">Assistant</a>
      <a class="<?= isActive('contact', $active) ?>" href="contact.php">Contact</a>
      <a class="<?= isActive('importance', $active) ?>" href="importance.php">Why Organic</a>
    </nav>
    <div class="nav-right">
      <span class="welcome">Hi, <?= e($user['name']) ?></span>
      <button class="ghost-btn" type="button" onclick="toggleTheme()">Theme</button>
      <a class="ghost-btn link-btn" href="logout.php">Logout</a>
    </div>
  </header>

  <?php if ($flash !== null): ?>
  <div class="flash flash-<?= e($flash['type']) ?>"><?= e($flash['message']) ?></div>
  <?php endif; ?>
<?php
}

function renderFooter(): void
{
    ?>
  <footer class="site-footer">
    <p>Organic Farming Platform - SDLC + DBMS Project (2026)</p>
  </footer>
  <script src="script.js"></script>
</body>
</html>
<?php
}
