<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>DevCard — Build Your Developer Portfolio</title>

    <meta name="description"
          content="Create a professional developer portfolio.
          Showcase your skills, projects, and experience with DevCard.">

    <meta name="robots" content="index, follow">

    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>

    <header class="navbar">
        <a class="logo" href="/">DevCard<span>.</span></a>

        <nav>
            <a href="#features">Features</a>
            <a href="#how-it-works">How it works</a>
            <a href="#builder" class="nav-button">Get started</a>
        </nav>
    </header>

    <main>
        <section class="hero">
            <p class="eyebrow">YOUR CAREER. YOUR CODE. YOUR STORY.</p>

            <h1>Your skills deserve<br>
                <span>a better showcase.</span>
            </h1>

            <p class="hero-description">
                Build a professional developer portfolio,
                showcase your projects, and share your work
                with the world.
            </p>

            <a href="#builder" class="primary-button">
                Build my portfolio &rarr;
            </a>

            <p class="small-note">
                Simple to create. Easy to share.
            </p>
        </section>

        <section id="features" class="features">
            <div class="section-heading">
                <p class="eyebrow">WHAT YOU GET</p>
                <h2>Everything you need to stand out.</h2>
            </div>

            <div class="feature-grid">
                <article class="feature-card">
                    <span class="feature-number">01</span>
                    <h3>Your profile</h3>
                    <p>Showcase your professional bio, skills,
                       and contact information.</p>
                </article>

                <article class="feature-card">
                    <span class="feature-number">02</span>
                    <h3>Your projects</h3>
                    <p>Present your best work with descriptions,
                       technologies, and project links.</p>
                </article>

                <article class="feature-card">
                    <span class="feature-number">03</span>
                    <h3>Your public page</h3>
                    <p>Share a portfolio link with potential
                       clients and employers.</p>
                </article>
            </div>
        </section>

        <section id="how-it-works" class="how-section">
            <p class="eyebrow">THREE SIMPLE STEPS</p>
            <h2>From idea to online portfolio.</h2>
            <p>Create your profile. Add your projects.
               Publish your portfolio.</p>
        </section>

        <section id="builder" class="cta-section">
            <h2>Ready to showcase your work?</h2>
            <p>Your next opportunity starts with how you
               present yourself.</p>
            <button id="startButton" class="primary-button">
                Start building
            </button>
        </section>
    </main>

    <footer>
        <a class="logo" href="/">DevCard<span>.</span></a>
        <p>Built for developers who are going places.</p>
        <p>&copy; <?php echo date('Y'); ?> DevCard</p>
    </footer>

    <script src="assets/js/app.js"></script>
</body>
</html>