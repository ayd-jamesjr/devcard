
<?php

require_once __DIR__ . '/app/auth.php';
require_once __DIR__ . '/app/db.php';

if (!empty($_SESSION['user_id'])) {
    header('Location: dashboard.php');
    exit;
}

$name = '';
$email = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $email = strtolower(trim($_POST['email'] ?? ''));
    $password = $_POST['password'] ?? '';
    $confirmPassword = $_POST['confirm_password'] ?? '';
    $token = $_POST['csrf_token'] ?? null;

    if (!csrf_is_valid($token)) {
        $error = 'Your session expired. Refresh the page and try again.';
    } elseif ($name === '' || strlen($name) > 120) {
        $error = 'Enter a name of up to 120 characters.';
    } elseif (
        !filter_var($email, FILTER_VALIDATE_EMAIL)
        || strlen($email) > 190
    ) {
        $error = 'Enter a valid email address.';
    } elseif (strlen($password) < 8) {
        $error = 'Your password must be at least 8 characters.';
    } elseif (strlen($password) > 72) {
        $error = 'Your password must not exceed 72 bytes.';
    } elseif ($password !== $confirmPassword) {
        $error = 'Your passwords do not match.';
    } else {
        try {
            $passwordHash = password_hash(
                $password,
                PASSWORD_DEFAULT
            );

            $stmt = $pdo->prepare(
                'INSERT INTO users (name, email, password_hash)
                 VALUES (:name, :email, :password_hash)'
            );

            $stmt->execute([
                'name' => $name,
                'email' => $email,
                'password_hash' => $passwordHash,
            ]);

            session_regenerate_id(true);

            $_SESSION['user_id'] = (int) $pdo->lastInsertId();
            $_SESSION['user_name'] = $name;

            // Give the next form a fresh token.
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));

            header('Location: dashboard.php');
            exit;

        } catch (PDOException $exception) {
            error_log($exception->getMessage());

            if ($exception->getCode() === '23000') {
                $error = 'That email may already be registered.';
            } else {
                $error = 'We could not create your account. Please try again.';
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Account | DevCard</title>
    <link rel="stylesheet" href="assets/css/auth.css">
</head>
<body>
    <main class="auth-card">
        <a class="brand" href="index.php">DevCard<span>.</span></a>

        <h1>Create your account</h1>
        <p class="subtitle">
            Start building your developer portfolio.
        </p>

        <?php if ($error !== ''): ?>
            <p class="error" role="alert"><?= e($error) ?></p>
        <?php endif; ?>

        <form method="POST" action="register.php">
            <input
                type="hidden"
                name="csrf_token"
                value="<?= e($_SESSION['csrf_token']) ?>"
            >

            <label for="name">Full name</label>
            <input
                id="name"
                name="name"
                type="text"
                maxlength="120"
                autocomplete="name"
                value="<?= e($name) ?>"
                required
            >

            <label for="email">Email address</label>
            <input
                id="email"
                name="email"
                type="email"
                maxlength="190"
                autocomplete="email"
                value="<?= e($email) ?>"
                required
            >

            <label for="password">Password</label>
            <input
                id="password"
                name="password"
                type="password"
                minlength="8"
                maxlength="72"
                autocomplete="new-password"
                required
            >

            <label for="confirm_password">Confirm password</label>
            <input
                id="confirm_password"
                name="confirm_password"
                type="password"
                minlength="8"
                maxlength="72"
                autocomplete="new-password"
                required
            >

            <button type="submit">Create account</button>
        </form>

        <p class="footer-text">
            Already have an account?
            <a href="login.php">Log in</a>
        </p>
    </main>
</body>
</html>
