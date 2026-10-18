<?php
declare(strict_types=1);

require_once __DIR__ . '/app/db.php';

function public_h($value): string
{
    return htmlspecialchars(
        (string) ($value ?? ''),
        ENT_QUOTES | ENT_SUBSTITUTE,
        'UTF-8'
    );
}

function public_url($url): string
{
    $url = trim((string) ($url ?? ''));

    if (
        $url !== '' &&
        filter_var($url, FILTER_VALIDATE_URL) &&
        strtolower((string) parse_url($url, PHP_URL_SCHEME)) === 'https'
    ) {
        return $url;
    }

    return '';
}

$slug = trim((string) ($_GET['slug'] ?? ''));

if ($slug === '' || strlen($slug) > 200) {
    http_response_code(404);
    exit('Portfolio not found. Please check the portfolio link.');
}

/*
|--------------------------------------------------------------------------
| Find the published portfolio
|--------------------------------------------------------------------------
*/
$stmt = $pdo->prepare(
    'SELECT
        p.id,
        p.slug,
        p.headline,
        p.bio,
        p.skills,
        p.github_url,
        p.linkedin_url,
        p.theme,
        u.name
     FROM portfolios AS p
     INNER JOIN users AS u ON u.id = p.user_id
     WHERE p.slug = :slug
       AND p.is_published = 1
     LIMIT 1'
);

$stmt->execute(['slug' => $slug]);
$portfolio = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$portfolio) {
    http_response_code(404);
    ?>
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Portfolio Not Found | DevCard</title>
        <style>
            body {
                margin: 0;
                padding: 40px 20px;
                background: #111;
                color: #f5f5f5;
                font-family: Arial, sans-serif;
                text-align: center;
            }

            a {
                color: #e4b64d;
            }
        </style>
    </head>
    <body>
        <h1>Portfolio not found</h1>
        <p>This portfolio does not exist or has not been published.</p>
        <p><a href="index.php">Return to DevCard</a></p>
    </body>
    </html>
    <?php
    exit;
}

/*
|--------------------------------------------------------------------------
| Load projects saved for this portfolio
|--------------------------------------------------------------------------
*/
$stmt = $pdo->prepare(
    'SELECT
        title,
        description,
        technologies,
        github_url,
        demo_url
     FROM projects
     WHERE portfolio_id = :portfolio_id
     ORDER BY id DESC'
);

$stmt->execute([
    'portfolio_id' => $portfolio['id']
]);

$projects = $stmt->fetchAll(PDO::FETCH_ASSOC);

/*
|--------------------------------------------------------------------------
| Split comma-separated skills into individual labels
|--------------------------------------------------------------------------
*/
$skills = array_filter(
    array_map(
        'trim',
        explode(',', (string) ($portfolio['skills'] ?? ''))
    ),
    fn($skill) => $skill !== ''
);

/*
|--------------------------------------------------------------------------
| Choose a supported theme
|--------------------------------------------------------------------------
*/
$theme = (($portfolio['theme'] ?? '') === 'light') ? 'light' : 'dark';

$background = ($theme === 'light') ? '#f5f5f5' : '#111111';
$surface = ($theme === 'light') ? '#ffffff' : '#191919';
$color = ($theme === 'light') ? '#171717' : '#f5f5f5';
$muted = ($theme === 'light') ? '#555555' : '#c6c6c6';
$border = ($theme === 'light') ? '#dddddd' : '#494949';

$description = trim(
    (string) ($portfolio['headline'] ?? '') . ' ' .
    (string) ($portfolio['bio'] ?? '')
);

if (function_exists('mb_substr')) {
    $description = mb_substr($description, 0, 155, 'UTF-8');
} else {
    $description = substr($description, 0, 155);
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title><?= public_h($portfolio['name']) ?> | DevCard Portfolio</title>
    <meta name="description" content="<?= public_h($description) ?>">
    <meta name="robots" content="index, follow">

    <style>
        * {
            box-sizing: border-box;
        }

        html {
            scroll-behavior: smooth;
        }

        body {
            margin: 0;
            background: <?= $background ?>;
            color: <?= $color ?>;
            font-family: Arial, Helvetica, sans-serif;
            line-height: 1.7;
        }

        a {
            color: #e4b64d;
            overflow-wrap: anywhere;
        }

        a:hover {
            opacity: .82;
        }

        .container {
            width: min(100% - 36px, 1000px);
            margin: 0 auto;
        }

        header {
            padding: 22px 0;
            border-bottom: 1px solid <?= $border ?>;
        }

        .brand {
            color: <?= $color ?>;
            text-decoration: none;
            font-size: 1.4rem;
            font-weight: 800;
        }

        .brand span,
        .eyebrow {
            color: #e4b64d;
        }

        .hero {
            padding: 70px 0 40px;
        }

        .eyebrow {
            font-size: .8rem;
            font-weight: 700;
            letter-spacing: 2px;
            text-transform: uppercase;
        }

        h1 {
            margin: 12px 0;
            font-size: clamp(2.2rem, 7vw, 4rem);
            line-height: 1.15;
            overflow-wrap: anywhere;
        }

        h2 {
            margin-top: 0;
            margin-bottom: 20px;
        }

        .headline {
            color: <?= $muted ?>;
            font-size: clamp(1.1rem, 3vw, 1.5rem);
        }

        .bio {
            max-width: 720px;
            white-space: pre-line;
        }

        .profile-links,
        .skills {
            display: flex;
            flex-wrap: wrap;
            gap: 12px;
            margin-top: 20px;
        }

        .profile-links a {
            display: inline-block;
            padding: 9px 14px;
            border: 1px solid <?= $border ?>;
            border-radius: 7px;
            text-decoration: none;
        }

        section {
            padding: 30px 0;
        }

        .skill {
            padding: 7px 12px;
            background: <?= $surface ?>;
            border: 1px solid <?= $border ?>;
            border-radius: 20px;
            font-size: .9rem;
        }

        .project-grid {
            display: grid;
            grid-template-columns:
                repeat(auto-fit, minmax(min(100%, 280px), 1fr));
            gap: 18px;
        }

        .project {
            min-width: 0;
            padding: 22px;
            background: <?= $surface ?>;
            border: 1px solid <?= $border ?>;
            border-radius: 10px;
        }

        .project h3 {
            margin-top: 0;
            overflow-wrap: anywhere;
        }

        .project-description {
            overflow-wrap: anywhere;
        }

        .muted {
            color: <?= $muted ?>;
        }

        footer {
            margin-top: 40px;
            padding: 24px 0;
            border-top: 1px solid <?= $border ?>;
            color: <?= $muted ?>;
            font-size: .9rem;
        }
    </style>
</head>

<body>

<header>
    <div class="container">
        <a class="brand" href="index.php">DevCard<span>.</span></a>
    </div>
</header>

<main class="container">

    <section class="hero">
        <div class="eyebrow">Developer Portfolio</div>

        <h1><?= public_h($portfolio['name']) ?></h1>

        <?php if (!empty($portfolio['headline'])): ?>
            <p class="headline">
                <?= public_h($portfolio['headline']) ?>
            </p>
        <?php endif; ?>

        <?php if (!empty($portfolio['bio'])): ?>
            <p class="bio"><?= public_h($portfolio['bio']) ?></p>
        <?php endif; ?>

        <div class="profile-links">
            <?php $github = public_url($portfolio['github_url']); ?>

            <?php if ($github !== ''): ?>
                <a href="<?= public_h($github) ?>"
                   target="_blank"
                   rel="noopener noreferrer">
                    GitHub Profile ↗
                </a>
            <?php endif; ?>

            <?php $linkedin = public_url($portfolio['linkedin_url']); ?>

            <?php if ($linkedin !== ''): ?>
                <a href="<?= public_h($linkedin) ?>"
                   target="_blank"
                   rel="noopener noreferrer">
                    LinkedIn Profile ↗
                </a>
            <?php endif; ?>
        </div>
    </section>

    <?php if (!empty($skills)): ?>
        <section>
            <h2>Skills &amp; Technologies</h2>

            <div class="skills">
                <?php foreach ($skills as $skill): ?>
                    <span class="skill">
                        <?= public_h($skill) ?>
                    </span>
                <?php endforeach; ?>
            </div>
        </section>
    <?php endif; ?>

    <section id="projects">
        <h2>Featured Projects</h2>

        <?php if (empty($projects)): ?>
            <p class="muted">
                No projects have been added to this portfolio yet.
            </p>
        <?php else: ?>
            <div class="project-grid">
                <?php foreach ($projects as $project): ?>
                    <article class="project">

                        <h3><?= public_h($project['title']) ?></h3>

                        <?php if (!empty($project['description'])): ?>
                            <p class="project-description">
                                <?= nl2br(public_h($project['description'])) ?>
                            </p>
                        <?php endif; ?>

                        <?php if (!empty($project['technologies'])): ?>
                            <p class="muted">
                                <strong>Built with:</strong>
                                <?= public_h($project['technologies']) ?>
                            </p>
                        <?php endif; ?>

                        <div class="profile-links">
                            <?php $repo = public_url($project['github_url']); ?>

                            <?php if ($repo !== ''): ?>
                                <a href="<?= public_h($repo) ?>"
                                   target="_blank"
                                   rel="noopener noreferrer">
                                    View Code ↗
                                </a>
                            <?php endif; ?>

                            <?php $demo = public_url($project['demo_url']); ?>

                            <?php if ($demo !== ''): ?>
                                <a href="<?= public_h($demo) ?>"
                                   target="_blank"
                                   rel="noopener noreferrer">
                                    Live Demo ↗
                                </a>
                            <?php endif; ?>
                        </div>

                    </article>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </section>

</main>

<footer>
    <div class="container">
        Portfolio powered by DevCard.
    </div>
</footer>

</body>
</html>
