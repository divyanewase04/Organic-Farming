<?php
declare(strict_types=1);

require_once __DIR__ . '/includes/db.php';

function yesNo(bool $value): string
{
    return $value ? 'Yes' : 'No';
}

$config = dbConfig();
$results = [];

$results[] = [
    'check' => 'PDO Extension Loaded',
    'value' => yesNo(extension_loaded('PDO')),
];
$results[] = [
    'check' => 'PDO MySQL Driver Loaded',
    'value' => yesNo(extension_loaded('pdo_mysql')),
];
$results[] = [
    'check' => 'Configured Host',
    'value' => (string) $config['host'],
];
$results[] = [
    'check' => 'Configured Port',
    'value' => (string) $config['port'],
];
$results[] = [
    'check' => 'Configured Database',
    'value' => (string) $config['database'],
];
$results[] = [
    'check' => 'Configured Username',
    'value' => (string) $config['username'],
];

$connectionStatus = 'Failed';
$connectionError = '';
try {
    $pdo = getPDO();
    $pdo->query('SELECT 1');
    $connectionStatus = 'Success';
} catch (Throwable $exception) {
    $connectionError = $exception->getMessage();
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Database Health Check</title>
  <style>
    body { font-family: Arial, sans-serif; margin: 20px; color: #1f2e24; }
    .card { max-width: 860px; border: 1px solid #d7e2d9; border-radius: 8px; padding: 16px; }
    table { width: 100%; border-collapse: collapse; margin-top: 14px; }
    th, td { border-bottom: 1px solid #e5ede6; text-align: left; padding: 10px; }
    th { width: 42%; background: #f6faf7; }
    .ok { color: #16602e; font-weight: 700; }
    .bad { color: #9b1c1c; font-weight: 700; }
    code { background: #f3f7f4; padding: 2px 6px; border-radius: 6px; }
  </style>
</head>
<body>
  <div class="card">
    <h1>Database Connectivity Guide</h1>
    <p>Status: <span class="<?= $connectionStatus === 'Success' ? 'ok' : 'bad' ?>"><?= htmlspecialchars($connectionStatus) ?></span></p>
    <?php if ($connectionError !== ''): ?>
    <p><strong>Error:</strong> <?= htmlspecialchars($connectionError) ?></p>
    <?php endif; ?>

    <table>
      <tbody>
        <?php foreach ($results as $row): ?>
        <tr>
          <th><?= htmlspecialchars($row['check']) ?></th>
          <td><?= htmlspecialchars($row['value']) ?></td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>

    <h2>Fix Steps</h2>
    <ol>
      <li>Start Apache and MySQL in XAMPP Control Panel.</li>
      <li>Open phpMyAdmin and import <code>sql/schema.sql</code>.</li>
      <li>If MySQL runs on another port, update <code>DB_PORT</code> or edit <code>includes/db.php</code>.</li>
      <li>Retry <code>register.php</code>.</li>
    </ol>
  </div>
</body>
</html>
