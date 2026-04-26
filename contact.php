<?php
declare(strict_types=1);

require_once __DIR__ . '/includes/bootstrap.php';
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/layout.php';

requireLogin();

$pdo = getPDO();
$user = currentUser();
$errors = [];

$name = $user['name'];
$email = $user['email'];
$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $token = $_POST['csrf_token'] ?? null;
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $message = trim($_POST['message'] ?? '');

    if (!validateCsrfToken($token)) {
        $errors[] = 'Invalid request token.';
    }
    if ($name === '' || strlen($name) < 3) {
        $errors[] = 'Please enter a valid name.';
    }
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Please enter a valid email address.';
    }
    if ($message === '' || strlen($message) < 8) {
        $errors[] = 'Message must be at least 8 characters.';
    }

    if ($errors === []) {
        $insert = $pdo->prepare(
            'INSERT INTO contact_messages (user_id, name, email, message, status) VALUES (?, ?, ?, ?, ?)'
        );
        $insert->execute([
            (int) $user['id'],
            $name,
            $email,
            $message,
            'NEW',
        ]);

        setFlash('success', 'Message submitted successfully. We will contact you soon.');
        header('Location: contact.php');
        exit();
    }
}

$csrfToken = generateCsrfToken();

renderHeader('Contact', 'contact');
?>
<main class="page-wrap">
  <section class="panel two-col">
    <article class="hero-callout">
      <h1>Contact Support</h1>
      <p class="muted">Raise questions about products, orders, delivery, and farm guidance.</p>
      <div class="highlight-grid">
        <div class="info-tile">
          <h3>Email</h3>
          <p>support@organicfarm.local</p>
        </div>
        <div class="info-tile">
          <h3>Phone</h3>
          <p>+91 98765 43210</p>
        </div>
        <div class="info-tile">
          <h3>Location</h3>
          <p>Pune, Maharashtra</p>
        </div>
        <div class="info-tile">
          <h3>Support Hours</h3>
          <p>Mon-Sat, 09:00-18:00</p>
        </div>
      </div>
    </article>

    <article class="hero-callout">
      <h2>Send Message</h2>
      <?php foreach ($errors as $error): ?>
      <p class="error-text"><?= e($error) ?></p>
      <?php endforeach; ?>
      <form method="post" class="contact-form">
        <input type="hidden" name="csrf_token" value="<?= e($csrfToken) ?>">

        <label for="name">Name</label>
        <input id="name" type="text" name="name" value="<?= e($name) ?>" required>

        <label for="email">Email</label>
        <input id="email" type="email" name="email" value="<?= e($email) ?>" required>

        <label for="message">Message</label>
        <textarea id="message" name="message" rows="4" required><?= e($message) ?></textarea>

        <button class="primary-btn" type="submit">Submit</button>
      </form>
    </article>
  </section>
</main>
<?php renderFooter(); ?>
