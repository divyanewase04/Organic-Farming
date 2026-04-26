<?php
declare(strict_types=1);

require_once __DIR__ . '/includes/bootstrap.php';
require_once __DIR__ . '/includes/db.php';

if (isLoggedIn()) {
    header('Location: index.php');
    exit();
}

$errors = [];
$fullName = '';
$email = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $fullName = trim($_POST['full_name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = (string) ($_POST['password'] ?? '');
    $confirmPassword = (string) ($_POST['confirm_password'] ?? '');
    $token = $_POST['csrf_token'] ?? null;

    if (!validateCsrfToken($token)) {
        $errors[] = 'Session expired. Please refresh and submit again.';
    }

    if ($fullName === '' || strlen($fullName) < 3) {
        $errors[] = 'Name must be at least 3 characters.';
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Please enter a valid email.';
    }

    if (strlen($password) < 6) {
        $errors[] = 'Password must be at least 6 characters.';
    }

    if ($password !== $confirmPassword) {
        $errors[] = 'Password and confirmation must match.';
    }

    if ($errors === []) {
        $pdo = getPDO();
        $existingUserCheck = $pdo->prepare('SELECT id FROM users WHERE email = ?');
        $existingUserCheck->execute([$email]);

        if ($existingUserCheck->fetch()) {
            $errors[] = 'Email is already registered.';
        } else {
            $hash = password_hash($password, PASSWORD_DEFAULT);
            $insert = $pdo->prepare(
                'INSERT INTO users (full_name, email, password_hash, role) VALUES (?, ?, ?, ?)'
            );
            $insert->execute([$fullName, $email, $hash, 'customer']);

            setFlash('success', 'Account created successfully. Please login.');
            header('Location: login.php');
            exit();
        }
    }
}

$csrfToken = generateCsrfToken();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Register | Organic Farming</title>
  <link rel="stylesheet" href="style.css">
</head>
<body class="auth-page">
  <main class="auth-shell">
    <section class="auth-card">
      <h1>Create Account</h1>
      <p class="muted">Register as a customer to place organic produce orders.</p>

      <?php foreach ($errors as $error): ?>
      <p class="error-text"><?= e($error) ?></p>
      <?php endforeach; ?>

      <form method="post" class="auth-form">
        <input type="hidden" name="csrf_token" value="<?= e($csrfToken) ?>">

        <label for="full_name">Full Name</label>
        <input id="full_name" type="text" name="full_name" required value="<?= e($fullName) ?>">

        <label for="email">Email</label>
        <input id="email" type="email" name="email" required value="<?= e($email) ?>">

        <label for="password">Password</label>
        <input id="password" type="password" name="password" required>

        <label for="confirm_password">Confirm Password</label>
        <input id="confirm_password" type="password" name="confirm_password" required>

        <button class="primary-btn" type="submit">Register</button>
      </form>

      <p class="helper-text">
        Already have an account? <a href="login.php">Login</a>
      </p>
    </section>
  </main>
</body>
</html>
