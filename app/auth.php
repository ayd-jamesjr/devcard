
<?php

// Start the session before sending any HTML output.
if (session_status() === PHP_SESSION_NONE) {
    if (headers_sent($file, $line)) {
        error_log(
            "DevCard session error: Output started in {$file} on line {$line} before the session started."
        );
    } else {
        $isHttps = (
            isset($_SERVER['HTTPS'])
            && $_SERVER['HTTPS'] !== ''
            && strtolower((string) $_SERVER['HTTPS']) !== 'off'
        );

        // Configure secure session cookies.
        session_set_cookie_params([
            'lifetime' => 0,
            'path' => '/',
            'secure' => $isHttps,
            'httponly' => true,
            'samesite' => 'Lax',
        ]);

        // Start the session.
        session_start();
    }
}

// Create a CSRF token for form security.
if (session_status() === PHP_SESSION_ACTIVE) {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
}

// Safely escape text before displaying it in HTML.
if (!function_exists('e')) {
    function e(string $value): string
    {
        return htmlspecialchars(
            $value,
            ENT_QUOTES | ENT_SUBSTITUTE,
            'UTF-8'
        );
    }
}

// Validate submitted CSRF tokens.
if (!function_exists('csrf_is_valid')) {
    function csrf_is_valid(mixed $token): bool
    {
        return is_string($token)
            && isset($_SESSION['csrf_token'])
            && hash_equals($_SESSION['csrf_token'], $token);
    }
}

// Require users to be logged in before accessing protected pages.
if (!function_exists('require_login')) {
    function require_login(): void
    {
        if (empty($_SESSION['user_id'])) {
            header('Location: login.php');
            exit;
        }
    }
}
