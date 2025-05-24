<?php
    session_start();
    require_once(__DIR__ . '/../database/service_db.php');
    require_once(__DIR__ . '/gig_card.php');


    $search = $_GET['search'] ?? '';
    $category = $_GET['category'] ?? '';
    $delivery_time = $_GET['delivery_time'] ?? '';
    $min_price = $_GET['min_price'] ?? '';
    $max_price = $_GET['max_price'] ?? '';

    $services = Service::search($search, $category, $delivery_time, $min_price, $max_price);
?>

<!DOCTYPE html>
<html lang="en">
    <head>
        <title>Taskly | The website that solves your problems</title>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <link rel="stylesheet" href="css/style.css">
        <link rel="stylesheet" href="css/gigs.css">
        <link rel="stylesheet" href="css/gig_card.css">
        <link rel="icon" href="../docs/logo.png" type="icon/x-icon">
        <script src="js/gig_card.js" defer></script>
        <script src="js/images.js" defer></script>

    </head>
    <body>
        <?php include_once(__DIR__ . '/../components/header.php'); ?>
        <main class="main-layout">
            <aside class="filters">
                <h3>Filters</h3>
                <form method="get" action="gigs.php">
                    <label>Category:
                        <select name="category">
                            <option value="">All</option>
                            <option value="Writing" <?= ($_GET['category'] ?? '') === 'Writing' ? 'selected' : '' ?>>Writing</option>
                            <option value="Graphic_Design" <?= ($_GET['category'] ?? '') === 'Graphic_Design' ? 'selected' : '' ?>>Graphic Design</option>
                            <option value="Web_App_Development" <?= ($_GET['category'] ?? '') === 'Web_App_Development' ? 'selected' : '' ?>>Web & App Development</option>
                            <option value="Digital_Marketing" <?= ($_GET['category'] ?? '') === 'Digital_Marketing' ? 'selected' : '' ?>>Digital Marketing</option>
                            <option value="Image_Video" <?= ($_GET['category'] ?? '') === 'Image_Video' ? 'selected' : '' ?>>Image & Video</option>
                            <option value="Translation" <?= ($_GET['category'] ?? '') === 'Translation' ? 'selected' : '' ?>>Translation</option>
                            <option value="Business_Consulting" <?= ($_GET['category'] ?? '') === 'Business_Consulting' ? 'selected' : '' ?>>Business Consulting</option>
                            <option value="Audio_Music" <?= ($_GET['category'] ?? '') === 'Audio_Music' ? 'selected' : '' ?>>Audio & Music</option>
                        </select>
                    </label>
                    <label>Delivery Time:
                        <select name="delivery_time">
                            <option value="">Any</option>
                            <option value="less_than_1_day" <?= ($_GET['delivery_time'] ?? '') === 'less_than_1_day' ? 'selected' : '' ?>>Less than a day</option>
                            <option value="1_2_days" <?= ($_GET['delivery_time'] ?? '') === '1_2_days' ? 'selected' : '' ?>>1-2 days</option>
                            <option value="less_than_a_week" <?= ($_GET['delivery_time'] ?? '') === 'less_than_a_week' ? 'selected' : '' ?>>Less than a week</option>
                            <option value="more_than_a_week" <?= ($_GET['delivery_time'] ?? '') === 'more_than_a_week' ? 'selected' : '' ?>>More than a week</option>
                        </select>
                    </label>
                    <label>Price Range:
                        <input type="number" placeholder="Min €" name="min_price" value="<?= htmlspecialchars($_GET['min_price'] ?? '') ?>" />
                        <input type="number" placeholder="Max €" name="max_price" value="<?= htmlspecialchars($_GET['max_price'] ?? '') ?>" />
                    </label>
                    <input type="text" name="search" value="<?= htmlspecialchars($search) ?>" hidden>
                    <button class="apply-filters" type="submit">Apply Filters</button>
                </form>
            </aside>

            <section class="content-area">

                <h1 class="page-title">Available Gigs</h1>
                
                <div class ="search-container">
                    <form action="gigs.php" method="get" style="display: flex; width: 100%;">
                        <input type="text" placeholder="Search for gigs..." class="search-input" name="search" value="<?= htmlspecialchars($search) ?>">
                        <button class="search-button" type="submit">
                            <img src="../docs/loupe.png" alt="Search" class="search-icon">
                        </button>
                    </form>
                </div>

                <div class="gigs-container" id="gigsContainer">
                    <?php
                    if (!empty($services)) {
                        foreach ($services as $service) {
                            renderGigCard($service, false); // Pass false to hide settings
                        }
                    } else {
                        echo '<p>No gigs found.</p>';
                    }
                    ?>
                </div>
            </section>
    </main>
    </body>
</html>