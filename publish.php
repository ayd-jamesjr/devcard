<?php

require_once __DIR__ . '/app/auth.php';
require_once __DIR__ . '/app/db.php';

require_login();

$userId = (int) $_SESSION['user_id'];
$error = '';
$success = isset($_GET['published']);

$stmt = $pdo->prepare(
    'SELECT id, slug, is_published
     FROM portfolios
     WHERE user_id = :user_id
     LIMIT 1'
);

$stmt->execute(['user_id' => $userId]);
$portfolio = $stmt->fetch();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    if (!csrf_is_valid($_POST['csrf_token'] ?? null)) {
        $error = 'Your session expired. Refresh and try again.';

    } elseif (!$portfolio) {
        $error = 'No portfolio found. Save your profile first.';

    } elseif (empty($portfolio['slug'])) {
        $error = 'Your portfolio has no URL slug. Save your profile again.';

    } else {
        try {
            $update = $pdo->prepare(
                'UPDATE portfolios
                 SET is_published = 1
                 WHERE id = :id
                   AND user_id = :user_id'
            );

            $update->execute([
                'id' => $portfolio['id'],
                'user_id' => $userId,
            ]);

            // Verify that the database now marks it as published.
            $check = $pdo->prepare(
                'SELECT is_published
                 FROM portfolios
                 WHERE id = :id
                   AND user_id = :user_id
                 LIMIT 1'
            );

            $check->execute([
                'id' => $portfolio['id'],
                'user_id' => $userId,
            ]);

            $status = $check->fetchColumn();

            if ((int) $status === 1) {
                header('Location: publish.php?published=1');
                exit;
            }

            $error = 'The database did not confirm publication.';

        } catch (PDOException $exception) {
            error_log('DevCard publishing error: ' . $exception->getMessage());
            $error = 'Could not publish the portfolio. Check the PHP error log.';
        }
    }
}

// Refresh portfolio status for display.
$stmt = $pdo->prepare(
    'SELECT id, slug, is_published
     FROM portfolios
     WHERE user_id = :user_id
     LIMIT 1'
);

$stmt->execute(['user_id' => $userId]);
$portfolio = $stmt->fetch();

$publicUrl = '';

if ($portfolio && !empty($portfolio['slug'])) {
    $publicUrl = 'public.php?slug=' . rawurlencode($portfolio['slug']);
}

function publish_h($value): string
{
    return htmlspecialchars(
        (string) ($value ?? ''),
        ENT_QUOTES | ENT_SUBSTITUTE,
        'UTF-8'
    );
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Publish Portfolio | DevCard</title>
    <link rel="stylesheet" href="assets/css/auth.css">

    <style>
        .publish-card { max-width: 650px; }

        .publish-panel {
            margin-top: 24px;
            padding: 22px;
            background: #191919;
            border: 1px solid #494949;
            border-radius: 10px;
        }

        .public-link {
            display: block;
            margin: 14px 0;
            padding: 12px;
            border: 1px solid #494949;
            border-radius: 7px;
            color: #e4b64d;
            overflow-wrap: anywhere;
        }

        .publish-button {
            margin-top: 14px;
            padding: 12px 16px;
            border: 1px solid #e4b64d;
            border-radius: 7px;
            background: #e4b64d;
            color: #171717;
            font-weight: 700;
            cursor: pointer;
        }

        .error { color: #ffbcbc; }
        .success { color: #b8f3c8; }
    </style>
</head>
<body>

<main class="auth-card publish-card">

    <a class="brand" href="index.php">DevCard<span>.</span></a>

    <h1>Publish your portfolio</h1>

    <p class="subtitle">
        Make your professional profile and projects visible to visitors.
    </p>

    <?php if ($error !== ''): ?>
        <p class="error" role="alert"><?= publish_h($error) ?></p>
    <?php endif; ?>

    <section class="publish-panel">

        <?php if (!$portfolio): ?>

            <h2>Portfolio profile required</h2>
            <p>Save your profile before publishing.</p>
            <a href="portfolio.php">Open portfolio editor</a>

        <?php elseif ((int) $portfolio['is_published'] === 1): ?>

            <h2 class="success">Your portfolio is published!</h2>
            <p>Anyone with this link can view your public portfolio.</p>

            <a class="public-link" href="<?= publish_h($publicUrl) ?>">
                <?= publish_h($publicUrl) ?>
            </a>

            <p>
                <a href="<?= publish_h($publicUrl) ?>" target="_blank"
                   rel="noopener noreferrer">
                    View public portfolio
                </a>
            </p>

        <?php else: ?>

            <h2>Your portfolio is currently private</h2>

            <p>
                Publishing will make your profile and project details
                visible to anyone who visits your public link.
            </p>

            <form method="POST" action="publish.php">
                <input
                    type="hidden"
                    name="csrf_token"
                    value="<?= publish_h($_SESSION['csrf_token']) ?>"
                >

                <button class="publish-button" type="submit">
                    Publish my portfolio
                </button>
            </form>

        <?php endif; ?>

    </section>

    <p>
        <a href="dashboard.php">Back to dashboard</a>
        &nbsp; | &nbsp;
        <a href="portfolio.php">Edit profile</a>
        &nbsp; | &nbsp;
        <a href="projects.php">Manage projects</a>
    </p>

</main>

</body>
</html>
