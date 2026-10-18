
<?php

require_once __DIR__ . '/app/auth.php';
require_once __DIR__ . '/app/db.php';

require_login();

$userId = (int) $_SESSION['user_id'];

// Load the logged-in user's account.
$stmt = $pdo->prepare(
    'SELECT id, name, email
     FROM users
     WHERE id = :id
     LIMIT 1'
);

$stmt->execute(['id' => $userId]);
$user = $stmt->fetch();

if (!$user) {
    $_SESSION = [];

    if (ini_get('session.use_cookies')) {
        $params = session_get_cookie_params();

        setcookie(
            session_name(),
            '',
            time() - 42000,
            $params['path'],
            $params['domain'],
            $params['secure'],
            $params['httponly']
        );
    }

    session_destroy();

    header('Location: login.php');
    exit;
}

$_SESSION['user_name'] = $user['name'];

// Check whether the user has created a portfolio.
$stmt = $pdo->prepare(
    'SELECT id, slug, is_published
     FROM portfolios
     WHERE user_id = :user_id
     LIMIT 1'
);

$stmt->execute(['user_id' => $userId]);
$portfolio = $stmt->fetch();

$hasPortfolio = (bool) $portfolio;
$isPublished = $hasPortfolio
    && (int) $portfolio['is_published'] === 1;

$publicUrl = '';

if ($isPublished && !empty($portfolio['slug'])) {
    $publicUrl = 'public.php?slug='
        . rawurlencode($portfolio['slug']);
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Dashboard | DevCard</title>
    <link rel="stylesheet" href="assets/css/auth.css">

    <style>
        .dashboard-card {
            max-width: 750px;
        }

        .welcome,
        .workspace-panel {
            margin-top: 24px;
            padding: 20px;
            border: 1px solid #494949;
            border-radius: 10px;
            background: #191919;
        }

        .welcome h1 {
            margin-top: 0;
        }

        .account-details {
            color: #c6c6c6;
            line-height: 1.9;
            overflow-wrap: anywhere;
        }

        .dashboard-actions {
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            gap: 12px;
            margin-top: 20px;
        }

        .dashboard-actions a,
        .dashboard-actions button {
            display: inline-block;
            padding: 12px 16px;
            border-radius: 8px;
            text-decoration: none;
            font-weight: 700;
            font-family: inherit;
            font-size: 14px;
            cursor: pointer;
        }

        .primary-action {
            background: #e4b64d;
            color: #171717;
            border: 1px solid #e4b64d;
        }

        .secondary-action {
            background: transparent;
            color: #f5f5f5;
            border: 1px solid #494949;
        }

        .dashboard-actions form {
            margin: 0;
        }

        .primary-action:hover {
            background: #f0c968;
        }

        .secondary-action:hover {
            background: #292929;
        }

        .status {
            margin-top: 12px;
            color: #c6c6c6;
            overflow-wrap: anywhere;
        }

        .status strong {
            color: #e4b64d;
        }

        .public-link {
            display: block;
            margin-top: 12px;
            overflow-wrap: anywhere;
            color: #e4b64d;
        }
    </style>
</head>

<body>
<main class="auth-card dashboard-card">

    <a class="brand" href="index.php">
        DevCard<span>.</span>
    </a>

    <section class="welcome">
        <h1>Welcome, <?= e($user['name']) ?>!</h1>

        <p class="subtitle">
            Your DevCard account is ready.
        </p>

        <div class="account-details">
            <p>
                <strong>Account ID:</strong>
                <?= (int) $user['id'] ?>
            </p>

            <p>
                <strong>Name:</strong>
                <?= e($user['name']) ?>
            </p>

            <p>
                <strong>Email:</strong>
                <?= e($user['email']) ?>
            </p>
        </div>
    </section>

    <section class="workspace-panel">
        <h2>Your portfolio workspace</h2>

        <p class="subtitle">
            Update your professional profile, manage projects,
            and publish your work for others to see.
        </p>

        <div class="dashboard-actions">
            <a class="primary-action" href="portfolio.php">
                Edit portfolio
            </a>

            <a class="secondary-action" href="projects.php">
                Manage projects
            </a>

            <a class="secondary-action" href="publish.php">
                Publish portfolio
            </a>
        </div>

        <p class="status">
            <strong>Portfolio status:</strong>
            <?php if (!$hasPortfolio): ?>
                Not created yet
            <?php elseif ($isPublished): ?>
                Published
            <?php else: ?>
                Saved — not published
            <?php endif; ?>
        </p>

        <?php if ($publicUrl !== ''): ?>
            <a class="public-link" href="<?= e($publicUrl) ?>">
                View your public portfolio
            </a>
        <?php endif; ?>
    </section>

    <div class="dashboard-actions">
        <form method="POST" action="logout.php">
            <input
                type="hidden"
                name="csrf_token"
                value="<?= e($_SESSION['csrf_token']) ?>"
            >

            <button type="submit" class="secondary-action">
                Log out
            </button>
        </form>
    </div>

</main>
</body>
</html>
