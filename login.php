<?php
declare(strict_types=1);

require_once __DIR__ . '/includes/bootstrap.php';
require_once __DIR__ . '/includes/db.php';

if (isLoggedIn()) {
    header('Location: index.php');
    exit();
}

$error = '';
$email = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = (string) ($_POST['password'] ?? '');
    $token = $_POST['csrf_token'] ?? null;

    if (!validateCsrfToken($token)) {
        $error = 'Session expired. Please refresh and try again.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL) || $password === '') {
        $error = 'Please enter a valid email and password.';
    } else {
        $pdo = getPDO();
        $statement = $pdo->prepare('SELECT id, full_name, email, password_hash, role FROM users WHERE email = ?');
        $statement->execute([$email]);
        $user = $statement->fetch();

        if ($user && password_verify($password, $user['password_hash'])) {
            session_regenerate_id(true);
            $_SESSION['user_id'] = (int) $user['id'];
            $_SESSION['user_name'] = $user['full_name'];
            $_SESSION['user_email'] = $user['email'];
            $_SESSION['user_role'] = $user['role'];

            setFlash('success', 'Welcome back, ' . $user['full_name'] . '.');
            header('Location: index.php');
            exit();
        }

        $error = 'Invalid credentials.';
    }
}

$csrfToken = generateCsrfToken();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Login | Organic Farming</title>
  <link rel="stylesheet" href="style.css">
</head>
<body class="auth-page">
  <main class="auth-shell">
    <section class="auth-card">
      <h1>Organic Farming Platform</h1>
      <p class="muted">Login to manage products, cart, and orders.</p>

      <?php if ($error !== ''): ?>
      <p class="error-text"><?= e($error) ?></p>
      <?php endif; ?>

      <form method="post" class="auth-form">
        <input type="hidden" name="csrf_token" value="<?= e($csrfToken) ?>">
        <label for="email">Email</label>
        <input id="email" type="email" name="email" required value="<?= e($email) ?>">

        <label for="password">Password</label>
        <input id="password" type="password" name="password" required>

        <button class="primary-btn" type="submit">Login</button>
      </form>

      <p class="helper-text">
        New user? <a href="register.php">Create account</a>
      </p>
    </section>
  </main>
</body>
</html>
