<?php

require_once __DIR__ . '/app/auth.php';
require_once __DIR__ . '/app/db.php';

require_login();

$userId = (int) $_SESSION['user_id'];
$error = '';
$success = '';

$stmt = $pdo->prepare(
    'SELECT * FROM portfolios WHERE user_id = :user_id LIMIT 1'
);
$stmt->execute(['user_id' => $userId]);
$portfolio = $stmt->fetch() ?: [];

$headline = $portfolio['headline'] ?? '';
$bio = $portfolio['bio'] ?? '';
$skills = $portfolio['skills'] ?? '';
$githubUrl = $portfolio['github_url'] ?? '';
$linkedinUrl = $portfolio['linkedin_url'] ?? '';
$theme = $portfolio['theme'] ?? 'dark';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $token = $_POST['csrf_token'] ?? null;

    $headline = trim($_POST['headline'] ?? '');
    $bio = trim($_POST['bio'] ?? '');
    $skills = trim($_POST['skills'] ?? '');
    $githubUrl = trim($_POST['github_url'] ?? '');
    $linkedinUrl = trim($_POST['linkedin_url'] ?? '');
    $theme = $_POST['theme'] ?? 'dark';

    if (!csrf_is_valid($token)) {
        $error = 'Your session expired. Refresh and try again.';
    } elseif (strlen($headline) > 180) {
        $error = 'Your headline must be 180 characters or fewer.';
    } elseif (strlen($bio) > 10000) {
        $error = 'Your bio is too long.';
    } elseif (strlen($skills) > 5000) {
        $error = 'Your skills list is too long.';
    } elseif (
        ($githubUrl !== '' &&
        (!filter_var($githubUrl, FILTER_VALIDATE_URL) ||
        parse_url($githubUrl, PHP_URL_SCHEME) !== 'https'))
        ||
        ($linkedinUrl !== '' &&
        (!filter_var($linkedinUrl, FILTER_VALIDATE_URL) ||
        parse_url($linkedinUrl, PHP_URL_SCHEME) !== 'https'))
    ) {
        $error = 'GitHub and LinkedIn links must be valid HTTPS URLs.';
    } elseif (!in_array($theme, ['dark', 'light'], true)) {
        $error = 'Choose a valid theme.';
    } else {
        $slugBase = strtolower(
            preg_replace('/[^a-zA-Z0-9]+/', '-', $_SESSION['user_name'])
        );
        $slugBase = trim($slugBase, '-');

        if ($slugBase === '') {
            $slugBase = 'developer';
        }

        try {
            $pdo->beginTransaction();

            $check = $pdo->prepare(
                'SELECT id, slug FROM portfolios WHERE user_id = :user_id LIMIT 1'
            );
            $check->execute(['user_id' => $userId]);
            $existing = $check->fetch();

            if ($existing) {
                $save = $pdo->prepare(
                    'UPDATE portfolios
                     SET headline = :headline,
                         bio = :bio,
                         skills = :skills,
                         github_url = :github_url,
                         linkedin_url = :linkedin_url,
                         theme = :theme
                     WHERE user_id = :user_id'
                );

                $save->execute([
                    'headline' => $headline,
                    'bio' => $bio,
                    'skills' => $skills,
                    'github_url' => $githubUrl ?: null,
                    'linkedin_url' => $linkedinUrl ?: null,
                    'theme' => $theme,
                    'user_id' => $userId,
                ]);
            } else {
                $slug = $slugBase . '-' . $userId;

                $save = $pdo->prepare(
                    'INSERT INTO portfolios
                     (user_id, slug, headline, bio, skills,
                      github_url, linkedin_url, theme, is_published)
                     VALUES
                     (:user_id, :slug, :headline, :bio, :skills,
                      :github_url, :linkedin_url, :theme, 0)'
                );

                $save->execute([
                    'user_id' => $userId,
                    'slug' => $slug,
                    'headline' => $headline,
                    'bio' => $bio,
                    'skills' => $skills,
                    'github_url' => $githubUrl ?: null,
                    'linkedin_url' => $linkedinUrl ?: null,
                    'theme' => $theme,
                ]);
            }

            $pdo->commit();

            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
            $success = 'Your portfolio details have been saved.';

        } catch (Throwable $exception) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }

            error_log($exception->getMessage());
            $error = 'Could not save your portfolio. Please try again.';
        }
    }
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Portfolio Editor | DevCard</title>
    <link rel="stylesheet" href="assets/css/auth.css">
    <style>
        .editor-card { max-width: 680px; }
        textarea { min-height: 120px; resize: vertical; }
        .notice { padding: 12px; border-radius: 8px; line-height: 1.5; }
        .success { background: #173522; color: #b8f3c9; }
        .field-help { color: #b7b7b7; font-size: 13px; }
        .back-link { color: #e4b64d; }
    </style>
</head>
<body>
<main class="auth-card editor-card">
    <a class="brand" href="dashboard.php">DevCard<span>.</span></a>
    <h1>Build your portfolio</h1>
    <p class="subtitle">
        Introduce yourself and show people what you can do.
    </p>

    <?php if ($error !== ''): ?>
        <p class="notice error" role="alert"><?= e($error) ?></p>
    <?php endif; ?>

    <?php if ($success !== ''): ?>
        <p class="notice success" role="status"><?= e($success) ?></p>
    <?php endif; ?>

    <form method="POST" action="portfolio.php">
        <input
            type="hidden"
            name="csrf_token"
            value="<?= e($_SESSION['csrf_token']) ?>"
        >

        <label for="headline">Professional headline</label>
        <input
            id="headline"
            name="headline"
            maxlength="180"
            placeholder="Software Engineering Student | Web Developer"
            value="<?= e($headline) ?>"
        >

        <label for="bio">About you</label>
        <textarea
            id="bio"
            name="bio"
            maxlength="10000"
            placeholder="Tell visitors who you are, what you build, and what you are learning."
        ><?= e($bio) ?></textarea>

        <label for="skills">Skills and technologies</label>
        <textarea
            id="skills"
            name="skills"
            maxlength="5000"
            placeholder="PHP, JavaScript, HTML, CSS, SQL"
        ><?= e($skills) ?></textarea>
        <p class="field-help">Separate skills with commas.</p>

        <label for="github_url">GitHub profile URL</label>
        <input
            id="github_url"
            name="github_url"
            type="url"
            placeholder="https://github.com/yourusername"
            value="<?= e($githubUrl) ?>"
        >

        <label for="linkedin_url">LinkedIn profile URL</label>
        <input
            id="linkedin_url"
            name="linkedin_url"
            type="url"
            placeholder="https://www.linkedin.com/in/yourusername"
            value="<?= e($linkedinUrl) ?>"
        >

        <label for="theme">Portfolio theme</label>
        <select id="theme" name="theme">
            <option value="dark" <?= $theme === 'dark' ? 'selected' : '' ?>>
                Dark
            </option>
            <option value="light" <?= $theme === 'light' ? 'selected' : '' ?>>
                Light
            </option>
        </select>

        <button type="submit">Save portfolio</button>
    </form>

    <p class="footer-text">
        <a class="back-link" href="dashboard.php">Back to dashboard</a>
    </p>
</main>
</body>
</html>
