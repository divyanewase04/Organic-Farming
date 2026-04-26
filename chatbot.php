<?php
declare(strict_types=1);

require_once __DIR__ . '/includes/bootstrap.php';
require_once __DIR__ . '/includes/layout.php';

requireLogin();

$csrfToken = generateCsrfToken();

renderHeader('Assistant', 'chatbot');
?>
<main class="page-wrap">
  <section class="panel chatbot-shell">
    <h1>Farming Assistant</h1>
    <p class="muted">Ask practical questions related to soil, nutrients, irrigation, pests, and market planning.</p>

    <div id="chat-panel" class="chat-panel"></div>

    <div class="quick-actions">
      <button class="secondary-btn chat-quick" data-message="How can I improve soil fertility?">Soil Fertility</button>
      <button class="secondary-btn chat-quick" data-message="What organic pest control methods should I use?">Pest Control</button>
      <button class="secondary-btn chat-quick" data-message="How should I schedule drip irrigation?">Irrigation</button>
      <button class="secondary-btn chat-quick" data-message="How do I plan market-ready harvest?">Market Planning</button>
    </div>

    <form id="chat-form" class="chat-form">
      <input id="chat-input" type="text" maxlength="400" placeholder="Type your question..." required>
      <button class="primary-btn" type="submit">Send</button>
    </form>
  </section>
</main>
<script>
window.CHATBOT_CSRF_TOKEN = "<?= e($csrfToken) ?>";
</script>
<?php renderFooter(); ?>
