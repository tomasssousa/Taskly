<!DOCTYPE html>
<html lang="en">
<head>
    <title>Taskly | The website that solves your problems</title>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/home.css">
    <link rel="icon" href="../docs/logo.png" type="icon/x-icon">
</head>
<body>
<?php include_once(__DIR__ . '/../components/header.php'); ?>
    <main>
        <h1 class="main-title">Search For Gigs</h1>
        <form class="search-container" action="../actions/action_homesearch.php" method="get">
            <input type="text" placeholder="Search for available tasks..." class="search-input" name="search">
            <button class="search-button" type="submit">
                <img src="../docs/loupe.png" alt="Search" class="search-icon">
            </button>
        </form>
        <section class="categories-section">
            <div class="categories-grid">
                <a href="gigs.php?category=<?php echo urlencode('Writing'); ?>" class="category-box">
                    <div class="category-icon">✍️</div>
                    <h3>Writing</h3>
                    <p>Everything related to writing from scratch</p>
                </a>
                <a href="gigs.php?category=<?php echo urlencode('Graphic Design'); ?>" class="category-box">
                    <div class="category-icon">🎨</div>
                    <h3>Graphic Design</h3>
                    <p>Everything related to graphic Design</p>
                </a>
                <a href="gigs.php?category=<?php echo urlencode('Web & App Development'); ?>" class="category-box">
                    <div class="category-icon">💻</div>
                    <h3>Web & App Development</h3>
                    <p>Everything related to Web and App development</p>
                </a>
                <a href="gigs.php?category=<?php echo urlencode('Digital Marketing'); ?>" class="category-box">
                    <div class="category-icon">📈</div>
                    <h3>Digital Marketing</h3>
                    <p>Everything related to Digital Marketing</p>
                </a>
                <a href="gigs.php?category=<?php echo urlencode('Image & Video'); ?>" class="category-box">
                    <div class="category-icon">📸</div>
                    <h3>Image & Video</h3>
                    <p>Everything related to Image and Video</p>
                </a>
                <a href="gigs.php?category=<?php echo urlencode('Translation'); ?>" class="category-box">
                    <div class="category-icon">🌐</div>
                    <h3>Translation</h3>
                    <p>Everything related to Translation</p>
                </a>
                <a href="gigs.php?category=<?php echo urlencode('Business Consulting'); ?>" class="category-box">
                    <div class="category-icon">💼</div>
                    <h3>Business Consulting</h3>
                    <p>Everything related to Bussiness Consulting</p>
                </a>
                <a href="gigs.php?category=<?php echo urlencode('Audio & Music'); ?>" class="category-box">
                    <div class="category-icon">🎵</div>
                    <h3>Audio & Music</h3>
                    <p>Everything related to Audio and Music production</p>
                </a>
            </div>
        </section>
    </main>
</body>
</html>