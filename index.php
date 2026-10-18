
<?php
// DevCard main homepage.
// Keep this page independent of login sessions so it can
// always open, even when a user is logged out.
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>DevCard | Build Your Developer Portfolio</title>

    <meta name="description"
          content="Create, manage, and publish your developer portfolio with DevCard.">

    <link rel="stylesheet" href="assets/css/style.css">

    <style>
        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            font-family: Arial, Helvetica, sans-serif;
            background: #0b0b0f;
            color: #f5f5f5;
        }

        a {
            color: inherit;
            text-decoration: none;
        }

        .navbar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 20px;
            padding: 20px 7%;
            border-bottom: 1px solid #292932;
        }

        .brand {
            font-size: 25px;
            font-weight: 800;
            color: #f5c451;
        }

        .nav-links {
            display: flex;
            align-items: center;
            flex-wrap: wrap;
            gap: 20px;
        }

        .nav-links a {
            font-size: 14px;
            color: #d1d1d8;
        }

        .nav-links a:hover {
            color: #f5c451;
        }

        .nav-links .nav-cta,
        .button-primary {
            background: #f5c451;
            color: #111;
            padding: 12px 18px;
            border-radius: 7px;
            font-weight: 700;
        }

        .hero {
            max-width: 1100px;
            margin: 0 auto;
            padding: 100px 24px 75px;
            text-align: center;
        }

        .eyebrow {
            color: #f5c451;
            font-weight: 700;
            letter-spacing: 2px;
            text-transform: uppercase;
            font-size: 12px;
        }

        h1 {
            font-size: clamp(38px, 7vw, 68px);
            line-height: 1.1;
            margin: 22px 0;
        }

        h1 span {
            color: #f5c451;
        }

        .hero p {
            max-width: 650px;
            margin: 0 auto;
            line-height: 1.8;
            color: #b8b8c4;
            font-size: 17px;
        }

        .hero-actions {
            display: flex;
            justify-content: center;
            flex-wrap: wrap;
            gap: 14px;
            margin-top: 30px;
        }

        .button-secondary {
            padding: 12px 18px;
            border: 1px solid #41414c;
            border-radius: 7px;
            font-weight: 700;
        }

        .section {
            max-width: 1100px;
            margin: 0 auto;
            padding: 35px 24px 80px;
        }

        .section h2 {
            font-size: 30px;
            margin-bottom: 12px;
        }

        .section-intro {
            color: #b8b8c4;
            line-height: 1.7;
            margin-bottom: 28px;
        }

        .feature-grid {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 18px;
        }

        .feature {
            display: block;
            background: #14141b;
            border: 1px solid #292932;
            padding: 24px;
            border-radius: 12px;
            transition: border-color 0.2s;
        }

        .feature:hover {
            border-color: #f5c451;
        }

        .feature h3 {
            margin-top: 0;
            margin-bottom: 10px;
        }

        .feature p {
            color: #b8b8c4;
            font-size: 14px;
            line-height: 1.7;
            margin-bottom: 0;
        }

        .feature .arrow {
            color: #f5c451;
            display: inline-block;
            margin-top: 18px;
            font-weight: 700;
        }

        .footer {
            border-top: 1px solid #292932;
            padding: 25px;
            text-align: center;
            color: #9999a6;
            font-size: 13px;
        }

        @media (max-width: 750px) {
            .navbar {
                align-items: flex-start;
                flex-direction: column;
            }

            .nav-links {
                gap: 15px;
            }

            .hero {
                padding-top: 65px;
            }

            .feature-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>

<body>

<header class="navbar">
    <a class="brand" href="index.php">DevCard.</a>

    <nav class="nav-links" aria-label="Main navigation">
        <a href="index.php">Home</a>
        <a href="login.php">Login</a>
        <a href="register.php" class="nav-cta">Create Account</a>
    </nav>
</header>

<main>
    <section class="hero">
        <div class="eyebrow">Your career. Your portfolio.</div>

        <h1>
            Build your portfolio.<br>
            <span>Show your greatness.</span>
        </h1>

        <p>
            DevCard helps developers create, manage, and publish
            professional portfolios to showcase their skills,
            projects, and experience.
        </p>

        <div class="hero-actions">
            <a href="register.php" class="button-primary">
                Get Started — It's Free
            </a>

            <a href="login.php" class="button-secondary">
                Log In
            </a>
        </div>
    </section>

    <section class="section">
        <h2>Everything you need in one place</h2>

        <p class="section-intro">
            Create an account and use the navigation below to access
            your portfolio-building tools.
        </p>

        <div class="feature-grid">

            <a class="feature" href="register.php">
                <h3>Create Your Account</h3>
                <p>
                    Sign up to start building your personal
                    developer portfolio.
                </p>
                <span class="arrow">Get started &rarr;</span>
            </a>

            <a class="feature" href="login.php">
                <h3>Login</h3>
                <p>
                    Sign in to access your existing DevCard account.
                </p>
                <span class="arrow">Sign in &rarr;</span>
            </a>

            <a class="feature" href="dashboard.php">
                <h3>Dashboard</h3>
                <p>
                    Manage your portfolio and access your
                    account tools. Login may be required.
                </p>
                <span class="arrow">Open dashboard &rarr;</span>
            </a>

            <a class="feature" href="portfolio.php">
                <h3>Portfolio Builder</h3>
                <p>
                    Edit your headline, biography, skills,
                    professional links, and portfolio theme.
                </p>
                <span class="arrow">Build portfolio &rarr;</span>
            </a>

            <a class="feature" href="projects.php">
                <h3>Manage Projects</h3>
                <p>
                    Add and manage the projects you want
                    visitors to see.
                </p>
                <span class="arrow">Manage projects &rarr;</span>
            </a>

            <a class="feature" href="publish.php">
                <h3>Publish Portfolio</h3>
                <p>
                    Publish your portfolio so other people
                    can view your work online.
                </p>
                <span class="arrow">Publish &rarr;</span>
            </a>

        </div>
    </section>
</main>

<footer class="footer">
    &copy; <?php echo date('Y'); ?> DevCard.
    Build. Showcase. Grow.
</footer>

</body>
</html>
