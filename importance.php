<?php
declare(strict_types=1);

require_once __DIR__ . '/includes/bootstrap.php';
require_once __DIR__ . '/includes/layout.php';

requireLogin();

renderHeader('Why Organic', 'importance');
?>
<main class="page-wrap">
  <section class="panel hero-callout">
    <h1>Importance of Organic Farming</h1>
    <p>
      Organic farming builds a long-term sustainable food ecosystem by reducing synthetic inputs, improving soil health,
      preserving biodiversity, and supporting farmer profitability through premium produce quality.
    </p>
    <div class="metric-chips">
      <span class="metric-chip">Soil Health</span>
      <span class="metric-chip">Water Safety</span>
      <span class="metric-chip">Biodiversity</span>
      <span class="metric-chip">Farmer Profitability</span>
    </div>
  </section>

  <section class="highlight-grid">
    <article class="info-tile">
      <h2>Environmental Benefits</h2>
      <ul class="plain-list">
        <li>Improves soil structure and microbial life.</li>
        <li>Reduces water pollution caused by chemical runoff.</li>
        <li>Supports pollinators and local biodiversity.</li>
        <li>Lowers carbon footprint with sustainable practices.</li>
      </ul>
    </article>

    <article class="info-tile">
      <h2>Farmer and Consumer Benefits</h2>
      <ul class="plain-list">
        <li>Better long-term soil fertility means lower input dependency.</li>
        <li>Healthier food with lower chemical residue risk.</li>
        <li>Higher market trust through traceable farm practices.</li>
        <li>Stronger resilience against climate and market volatility.</li>
      </ul>
    </article>
  </section>

  <section class="panel hero-callout">
    <h2>Recommended Practices</h2>
    <ul class="plain-list">
      <li>Crop rotation and mixed cropping for nutrient balancing.</li>
      <li>Compost and bio-fertilizer plans by crop stage.</li>
      <li>Integrated pest management using preventive controls first.</li>
      <li>Drip irrigation and mulching for efficient water management.</li>
    </ul>
  </section>
</main>
<?php renderFooter(); ?>
