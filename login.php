<?php
require_once __DIR__ . '/app/auth.php';
require_once __DIR__ . '/app/db.php';

// If already logged in, go to dashboard.
if (!empty($_SESSION['user_id'])) {
    header('Location: dashboard.php');
    exit;
}

$error = '';
$email = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $token = $_POST['csrf_token'] ?? '';

    if (!csrf_is_valid($token)) {
        $error = 'Your session expired. Refresh the page and try again.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL) || $password === '') {
        $error = 'Enter a valid email address and password.';
    } else {
        $stmt = $pdo->prepare(
            'SELECT id, name, email, password_hash
             FROM users
             WHERE email = ?
             LIMIT 1'
        );
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password_hash'])) {
            session_regenerate_id(true);

            $_SESSION['user_id'] = (int) $user['id'];
            $_SESSION['user_name'] = $user['name'];
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));

            header('Location: dashboard.php');
            exit;
        }

        $error = 'Incorrect email or password.';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Log In | DevCard</title>
    <link rel="stylesheet" href="assets/css/auth.css">
</head>
<body>
    <main class="auth-container">
        <h1>DevCard.</h1>
        <h2>Welcome back</h2>
        <p>Log in to manage your developer portfolio.</p>

        <?php if ($error !== ''): ?>
            <p class="error" role="alert">
                <?= e($error) ?>
            </p>
        <?php endif; ?>

        <form method="POST" action="login.php">
            <input
                type="hidden"
                name="csrf_token"
                value="<?= e($_SESSION['csrf_token']) ?>"
            >

            <label for="email">Email address</label>
            <input
                type="email"
                id="email"
                name="email"
                value="<?= e($email) ?>"
                autocomplete="email"
                required
            >

            <label for="password">Password</label>
            <input
                type="password"
                id="password"
                name="password"
                autocomplete="current-password"
                required
            >

            <button type="submit">Log In</button>
        </form>

        <p>Don't have an account?
            <a href="register.php">Create one</a>
        </p>
    </main>
</body>
</html>
