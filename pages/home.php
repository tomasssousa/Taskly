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
    </main>
</body>
</html>