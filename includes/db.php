<?php
declare(strict_types=1);

function dbConfig(): array
{
    return [
        'host' => getenv('DB_HOST') ?: '127.0.0.1',
        'port' => (int) (getenv('DB_PORT') ?: 3306),
        'database' => getenv('DB_NAME') ?: 'organic_farming',
        'username' => getenv('DB_USER') ?: 'root',
        'password' => getenv('DB_PASS') ?: '',
    ];
}

function dbDebugEnabled(): bool
{
    $value = getenv('APP_DEBUG');
    if ($value === false) {
        return true;
    }

    return in_array(strtolower((string) $value), ['1', 'true', 'yes', 'on'], true);
}

function getPDO(): PDO
{
    static $pdo = null;

    if ($pdo instanceof PDO) {
        return $pdo;
    }

    $config = dbConfig();
    $hostsToTry = array_values(array_unique([$config['host'], '127.0.0.1', 'localhost']));
    $lastError = 'Unknown database connection issue.';

    foreach ($hostsToTry as $host) {
        $dsn = sprintf(
            'mysql:host=%s;port=%d;dbname=%s;charset=utf8mb4',
            $host,
            $config['port'],
            $config['database']
        );

        try {
            $pdo = new PDO($dsn, $config['username'], $config['password'], [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
                PDO::ATTR_TIMEOUT => 3,
            ]);
            return $pdo;
        } catch (PDOException $exception) {
            $lastError = $exception->getMessage();
        }
    }

    http_response_code(500);
    echo 'Database connection failed.<br>';
    echo 'Check MySQL service, database import, and config in includes/db.php.<br>';
    echo 'Tried host(s): ' . implode(', ', $hostsToTry) . ' | Port: ' . (int) $config['port'] . ' | DB: ' . htmlspecialchars((string) $config['database']) . '<br>';
    echo 'Open <a href="db_health.php">db_health.php</a> for guided diagnostics.';

    if (dbDebugEnabled()) {
        echo '<pre style="white-space:pre-wrap;margin-top:10px;">' . htmlspecialchars($lastError) . '</pre>';
    }
    exit();
}
