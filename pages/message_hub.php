<!DOCTYPE html>
<html lang="en">
<head>
    <title>Taskly | The website that solves your problems</title>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="message_hub.css">
    <link rel="icon" href="../docs/logo.png" type="icon/x-icon">
</head>
<body>
<?php include_once(__DIR__ . '/../components/header.php'); ?>
<main>
    <div class="message-hub-container">
        <aside class="sidebar" id="sidebar">
            <h2>Messages</h2>
            <ul class="conversation-list">
                <li><a href="#">Tomás</a></li>
                <li><a href="#">Matilde</a></li>
                <li><a href="#">Gonçalo</a></li>
            </ul>
        </aside>
        <section class="messages-area">
        </section>
    </div>
</main>
<script>
    function toggleUserMenu() {
        const menu = document.getElementById('userMenu');
        menu.style.display = menu.style.display === 'block' ? 'none' : 'block';
    }
</script>
</body>
</html>