<?php

$projectRoot = dirname(__DIR__);
$envFile = $projectRoot . DIRECTORY_SEPARATOR . '.env';

/*
 * Load .env locally when available.
 * On Render, settings will come from environment variables.
 */
$config = [];

if (is_file($envFile)) {
    $parsedConfig = parse_ini_file(
        $envFile,
        false,
        INI_SCANNER_RAW
    );

    if ($parsedConfig === false) {
        throw new RuntimeException(
            'Could not read .env. Check its format.'
        );
    }

    $config = $parsedConfig;
}

/*
 * Environment variables take priority over local .env settings.
 */
$getSetting = static function (
    string $key,
    ?string $default = null
) use ($config): ?string {
    $value = getenv($key);

    if ($value !== false && $value !== '') {
        return $value;
    }

    if (isset($config[$key]) && $config[$key] !== '') {
        return (string) $config[$key];
    }

    return $default;
};

/*
 * Read required database settings.
 */
$requiredKeys = [
    'DB_HOST',
    'DB_PORT',
    'DB_NAME',
    'DB_USER',
    'DB_PASS'
];

$dbConfig = [];

foreach ($requiredKeys as $key) {
    $value = $getSetting($key);

    if ($value === null || $value === '') {
        throw new RuntimeException(
            'Missing required database setting: ' . $key
        );
    }

    $dbConfig[$key] = $value;
}

$dbHost = $dbConfig['DB_HOST'];
$dbPort = $dbConfig['DB_PORT'];
$dbName = $dbConfig['DB_NAME'];
$dbUser = $dbConfig['DB_USER'];
$dbPass = $dbConfig['DB_PASS'];

$options = [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES => false,
];

/*
 * Configure TLS for TiDB Cloud.
 * Keep certificate verification enabled.
 */
$sslEnabled = $getSetting('DB_SSL', '1') === '1';

if ($sslEnabled) {
    $caPathSetting = $getSetting(
        'DB_SSL_CA',
        'certs/tidb-ca.pem'
    );

    $isAbsolutePath =
        str_starts_with($caPathSetting, '/') ||
        (bool) preg_match(
            '/^[A-Za-z]:[\\\\\/]/',
            $caPathSetting
        );

    if ($isAbsolutePath) {
        $caPath = $caPathSetting;
    } else {
        $caPath = $projectRoot . DIRECTORY_SEPARATOR
            . str_replace(
                ['/', '\\'],
                DIRECTORY_SEPARATOR,
                $caPathSetting
            );
    }

    if (!is_file($caPath)) {
        throw new RuntimeException(
            'TiDB CA certificate not found. Check DB_SSL_CA.'
        );
    }

    $options[PDO::MYSQL_ATTR_SSL_CA] = $caPath;
    $options[PDO::MYSQL_ATTR_SSL_VERIFY_SERVER_CERT] = true;
}

/*
 * Connect to TiDB using PDO.
 */
$dsn = "mysql:host={$dbHost};port={$dbPort};dbname={$dbName};charset=utf8mb4";

$pdo = new PDO(
    $dsn,
    $dbUser,
    $dbPass,
    $options
);
