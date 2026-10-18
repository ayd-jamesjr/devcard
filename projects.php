
<?php

require_once __DIR__ . '/app/auth.php';
require_once __DIR__ . '/app/db.php';

require_login();

$userId = (int) $_SESSION['user_id'];
$error = '';
$success = '';

// Find this user's portfolio.
$stmt = $pdo->prepare(
    'SELECT id FROM portfolios WHERE user_id = :user_id LIMIT 1'
);

$stmt->execute(['user_id' => $userId]);
$portfolio = $stmt->fetch();

if (!$portfolio) {
    $portfolioId = null;
} else {
    $portfolioId = (int) $portfolio['id'];
}

// Handle add and delete actions.
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $token = $_POST['csrf_token'] ?? '';

    if (!csrf_is_valid($token)) {
        $error = 'Your session expired. Refresh the page and try again.';
    } elseif (!$portfolioId) {
        $error = 'Please save your portfolio profile before adding projects.';
    } else {
        $action = $_POST['action'] ?? '';

        if ($action === 'add') {
            $title = trim($_POST['title'] ?? '');
            $description = trim($_POST['description'] ?? '');
            $technologies = trim($_POST['technologies'] ?? '');
            $githubUrl = trim($_POST['github_url'] ?? '');
            $demoUrl = trim($_POST['demo_url'] ?? '');

            if ($title === '' || strlen($title) > 150) {
                $error = 'Enter a project title (maximum 150 characters).';
            } elseif ($description === '' || strlen($description) > 5000) {
                $error = 'Enter a description (maximum 5000 characters).';
            } elseif (strlen($technologies) > 1000) {
                $error = 'The technologies field is too long.';
            } elseif (
                ($githubUrl !== '' &&
                    (!filter_var($githubUrl, FILTER_VALIDATE_URL) ||
                     strtolower((string) parse_url($githubUrl, PHP_URL_SCHEME)) !== 'https'))
                ||
                ($demoUrl !== '' &&
                    (!filter_var($demoUrl, FILTER_VALIDATE_URL) ||
                     strtolower((string) parse_url($demoUrl, PHP_URL_SCHEME)) !== 'https'))
            ) {
                $error = 'GitHub and demo links must be valid HTTPS URLs.';
            } else {
                $stmt = $pdo->prepare(
                    'INSERT INTO projects
                    (portfolio_id, title, description, technologies,
                     github_url, demo_url)
                    VALUES
                    (:portfolio_id, :title, :description, :technologies,
                     :github_url, :demo_url)'
                );

                $stmt->execute([
                    'portfolio_id' => $portfolioId,
                    'title' => $title,
                    'description' => $description,
                    'technologies' => $technologies,
                    'github_url' => $githubUrl !== '' ? $githubUrl : null,
                    'demo_url' => $demoUrl !== '' ? $demoUrl : null,
                ]);

                header('Location: projects.php?added=1');
                exit;
            }
        } elseif ($action === 'delete') {
            $projectId = filter_var(
                $_POST['project_id'] ?? null,
                FILTER_VALIDATE_INT
            );

            if (!$projectId || $projectId < 1) {
                $error = 'Invalid project selected.';
            } else {
                // Delete only a project belonging to this user's portfolio.
                $stmt = $pdo->prepare(
                    'DELETE FROM projects
                     WHERE id = :project_id
                     AND portfolio_id = :portfolio_id'
                );

                $stmt->execute([
                    'project_id' => $projectId,
                    'portfolio_id' => $portfolioId,
                ]);

                if ($stmt->rowCount() > 0) {
                    header('Location: projects.php?deleted=1');
                    exit;
                }

                $error = 'Project not found or already deleted.';
            }
        } else {
            $error = 'Invalid action.';
        }
    }
}

if (isset($_GET['added'])) {
    $success = 'Project added successfully!';
} elseif (isset($_GET['deleted'])) {
    $success = 'Project deleted successfully.';
}

// Load this portfolio's projects.
$projects = [];

if ($portfolioId) {
    $stmt = $pdo->prepare(
        'SELECT id, title, description, technologies,
                github_url, demo_url, created_at
         FROM projects
         WHERE portfolio_id = :portfolio_id
         ORDER BY id DESC'
    );

    $stmt->execute(['portfolio_id' => $portfolioId]);
    $projects = $stmt->fetchAll();
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>My Projects | DevCard</title>
    <link rel="stylesheet" href="assets/css/auth.css">

    <style>
        .projects-container {
            width: 100%;
            max-width: 850px;
            margin: 0 auto;
        }

        .project-panel {
            margin-top: 24px;
            padding: 22px;
            background: #191919;
            border: 1px solid #494949;
            border-radius: 10px;
        }

        .project-form label {
            display: block;
            margin-top: 16px;
            margin-bottom: 7px;
            color: #f5f5f5;
            font-weight: 600;
        }

        .project-form input,
        .project-form textarea {
            width: 100%;
            box-sizing: border-box;
        }

        .project-form textarea {
            min-height: 100px;
            resize: vertical;
        }

        .project-form button,
        .delete-button {
            margin-top: 18px;
            padding: 11px 16px;
            border-radius: 7px;
            border: 1px solid #e4b64d;
            background: #e4b64d;
            color: #171717;
            font-weight: 700;
            cursor: pointer;
        }

        .project-item {
            margin-top: 16px;
            padding: 18px;
            border: 1px solid #494949;
            border-radius: 9px;
            overflow-wrap: anywhere;
        }

        .project-item h3 {
            margin-top: 0;
        }

        .project-links {
            display: flex;
            flex-wrap: wrap;
            gap: 16px;
            margin-top: 12px;
        }

        .project-links a,
        .back-link {
            color: #e4b64d;
        }

        .delete-form {
            display: inline-block;
        }

        .delete-button {
            margin-top: 14px;
            background: transparent;
            border-color: #b66b6b;
            color: #ffaaaa;
        }

        .notice {
            margin-top: 16px;
            padding: 12px;
            border-radius: 7px;
            overflow-wrap: anywhere;
        }

        .error-notice {
            background: #351d1d;
            color: #ffbcbc;
        }

        .success-notice {
            background: #193122;
            color: #b8f3c8;
        }

        .muted {
            color: #c6c6c6;
        }
    </style>
</head>
<body>

<main class="auth-card projects-container">

    <a class="brand" href="index.php">DevCard<span>.</span></a>

    <h1>My Projects</h1>
    <p class="subtitle">
        Showcase the applications and websites you have built.
    </p>

    <?php if ($error !== ''): ?>
        <div class="notice error-notice" role="alert">
            <?= e($error) ?>
        </div>
    <?php endif; ?>

    <?php if ($success !== ''): ?>
        <div class="notice success-notice" role="status">
            <?= e($success) ?>
        </div>
    <?php endif; ?>

    <?php if (!$portfolioId): ?>

        <section class="project-panel">
            <h2>Create your portfolio first</h2>
            <p class="muted">
                Save your portfolio profile before adding projects.
            </p>
            <a class="back-link" href="portfolio.php">
                Open portfolio editor
            </a>
        </section>

    <?php else: ?>

        <section class="project-panel">
            <h2>Add a project</h2>

            <form class="project-form" method="POST" action="projects.php">

                <input
                    type="hidden"
                    name="csrf_token"
                    value="<?= e($_SESSION['csrf_token']) ?>"
                >

                <input type="hidden" name="action" value="add">

                <label for="title">Project title *</label>
                <input
                    type="text"
                    id="title"
                    name="title"
                    maxlength="150"
                    required
                    placeholder="e.g. Client Management System"
                >

                <label for="description">Project description *</label>
                <textarea
                    id="description"
                    name="description"
                    maxlength="5000"
                    required
                    placeholder="What does your project do?"
                ></textarea>

                <label for="technologies">Technologies used</label>
                <input
                    type="text"
                    id="technologies"
                    name="technologies"
                    maxlength="1000"
                    placeholder="PHP, MySQL, HTML, CSS, JavaScript"
                >

                <label for="github_url">GitHub repository URL</label>
                <input
                    type="url"
                    id="github_url"
                    name="github_url"
                    placeholder="https://github.com/username/project"
                >

                <label for="demo_url">Live demo URL</label>
                <input
                    type="url"
                    id="demo_url"
                    name="demo_url"
                    placeholder="https://example.com"
                >

                <button type="submit">Save project</button>

            </form>
        </section>

        <section class="project-panel">
            <h2>Your saved projects (<?= count($projects) ?>)</h2>

            <?php if (count($projects) === 0): ?>

                <p class="muted">
                    No projects yet. Add your first project using the form above.
                </p>

            <?php else: ?>

                <?php foreach ($projects as $project): ?>

                    <article class="project-item">

                        <h3><?= e($project['title']) ?></h3>

                        <p><?= nl2br(e($project['description'])) ?></p>

                        <?php if (!empty($project['technologies'])): ?>
                            <p class="muted">
                                <strong>Technologies:</strong>
                                <?= e($project['technologies']) ?>
                            </p>
                        <?php endif; ?>

                        <div class="project-links">

                            <?php if (!empty($project['github_url'])): ?>
                                <a
                                    href="<?= e($project['github_url']) ?>"
                                    target="_blank"
                                    rel="noopener noreferrer"
                                >
                                    GitHub repository
                                </a>
                            <?php endif; ?>

                            <?php if (!empty($project['demo_url'])): ?>
                                <a
                                    href="<?= e($project['demo_url']) ?>"
                                    target="_blank"
                                    rel="noopener noreferrer"
                                >
                                    Live demo
                                </a>
                            <?php endif; ?>

                        </div>

                        <form
                            class="delete-form"
                            method="POST"
                            action="projects.php"
                            onsubmit="return confirm('Are you sure you want to delete this project?');"
                        >
                            <input
                                type="hidden"
                                name="csrf_token"
                                value="<?= e($_SESSION['csrf_token']) ?>"
                            >

                            <input type="hidden" name="action" value="delete">

                            <input
                                type="hidden"
                                name="project_id"
                                value="<?= (int) $project['id'] ?>"
                            >

                            <button class="delete-button" type="submit">
                                Delete project
                            </button>
                        </form>

                    </article>

                <?php endforeach; ?>

            <?php endif; ?>
        </section>

    <?php endif; ?>

    <p>
        <a class="back-link" href="dashboard.php">Back to dashboard</a>
        &nbsp; | &nbsp;
        <a class="back-link" href="portfolio.php">Edit portfolio profile</a>
    </p>

</main>

</body>
</html>
