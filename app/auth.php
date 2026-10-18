
<?php
/**
 * DevCard authentication and session helpers.
 * IMPORTANT: No spaces, HTML, or output before this opening PHP tag.
 */

// Start the session before sending HTML output.
if (session_status() === PHP_SESSION_NONE) {
    if (headers_sent($file, $line)) {
        error_log(
            "DevCard session error: Output started in {$file} on line {$line}."
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

        session_start();
    }
}

// Create a CSRF token when the session is active.
if (
    session_status() === PHP_SESSION_ACTIVE
    && empty($_SESSION['csrf_token'])
) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
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
            && session_status() === PHP_SESSION_ACTIVE
            && isset($_SESSION['csrf_token'])
            && hash_equals($_SESSION['csrf_token'], $token);
    }
}

// Require users to be logged in before accessing protected pages.
if (!function_exists('require_login')) {
    function require_login(): void
    {
        if (empty($_SESSION['user_id'])) {
            // Redirect to the site's login page.
            $scriptName = $_SERVER['SCRIPT_NAME'] ?? '';
            $basePath = rtrim(
                str_replace('\\', '/', dirname($scriptName)),
                '/'
            );

            if ($basePath === '/' || $basePath === '.') {
                $basePath = '';
            }

            header('Location: ' . $basePath . '/login.php');
            exit;
        }
    }
}
