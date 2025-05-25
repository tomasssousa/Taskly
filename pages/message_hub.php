<?php
session_start();

require_once(__DIR__ . '/../database/user_db.php');
require_once(__DIR__ . '/../database/chat_db.php');

if (!isset($_SESSION['username'])) {
    header('Location: login.php');
    exit();
}

$current_username = $_SESSION['username'];
$current_user = User::get_user($current_username);

if (!$current_user) {
    session_destroy();
    header('Location: login.php');
    exit();
}

$target_user = $_GET['user'] ?? null;
$action = $_GET['action'] ?? null;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Taskly | Messages</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/message_hub.css">
    <link rel="icon" href="../docs/logo.png" type="image/x-icon">
</head>

<body>
    <?php include_once(__DIR__ . '/../components/header.php'); ?>
    <main>
        <div class="message-hub-container">
            <aside class="sidebar" id="sidebar">
                <h2>Messages</h2>
                <ul class="conversation-list" id="conversationList">
                </ul>
            </aside>
            <section class="messages-area" id="messagesArea">
                <div class="no-chat-selected">
                    <p>Select a conversation to start messaging</p>
                </div>
            </section>
        </div>
    </main>
    <script>
        const currentUser = '<?= htmlspecialchars($current_username, ENT_QUOTES, 'UTF-8') ?>';
        const userTaskMode = <?= json_encode((bool)$current_user['task_mode']) ?>;
        <?php if ($target_user && $action === 'start_conversation'): ?>
        const startConversationWith = '<?= htmlspecialchars($target_user, ENT_QUOTES, 'UTF-8') ?>';
        <?php endif; ?>
    </script>
    <script src="js/message_hub.js"></script>
</body>
</html>