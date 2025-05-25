<?php
session_start();

require_once(__DIR__ . '/../database/service_db.php');
require_once(__DIR__ . '/../database/user_db.php');

$taskId = $_GET['id'] ?? null;

// Check if task ID is provided
if (!$taskId) {
    header('Location: home.php');
    exit();
}

$currentUser = null;
$isTasker = false;
$isFavorite = false;
$task = null;

// Get task data first
try {
    $task = Service::get_service_with_media($taskId);
    if (!$task) {
        header('Location: home.php');
        exit();
    }
    
    $images = !empty($task['all_images']) ? explode(',', $task['all_images']) : ['default-gig.jpg'];
    $userInfo = User::get_user($task['user']);
    $profilePhoto = $userInfo && !empty($userInfo['profile_photo']) ? $userInfo['profile_photo'] : null;

} catch (Exception $e) {
    die("Error: " . $e->getMessage());
}

// Check user authentication and favorites
if (isset($_SESSION['username'])) {
    $currentUser = User::get_user($_SESSION['username']);
    $isTasker = $currentUser && $currentUser['task_mode'];
    
    // Check if this service is in user's favorites with direct SQL
    try {
        require_once(__DIR__ . '/../database/connection.php');
        $db = Database::getInstance();
        $stmt = $db->prepare('SELECT COUNT(*) FROM favourite_services WHERE username = ? AND id = ?');
        $stmt->execute([$_SESSION['username'], $taskId]);
        $isFavorite = $stmt->fetchColumn() > 0;
    } catch (Exception $e) {
        error_log("Favorites check error: " . $e->getMessage());
        $isFavorite = false;
    }
} else {
    $isFavorite = false;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <title>Taskly | Task</title>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/task.css">
    <link rel="stylesheet" href="css/gig_card.css">  
    <link rel="icon" href="../docs/logo.png" type="icon/x-icon">
    <script src="js/gig_card.js" defer></script>
    <script src="js/images.js" defer></script>
    <script src="js/task.js" defer></script>
</head>
<body>
<?php include_once(__DIR__ . '/../components/header.php'); ?>

<main class="main-layout">
    <section class="content-area">
        <div class="task-container">
            <div class="task-grid">
                <div class="media-section">
                    <div class="carousel" data-gig-id="<?= $task['id'] ?>">
                        <?php foreach ($images as $index => $image): ?>
                            <img src="../docs/<?= htmlspecialchars($image) ?>" 
                                 alt="<?= htmlspecialchars($task['task_title']) ?>" 
                                 class="gig-image <?= $index === 0 ? 'active' : '' ?>"
                                 data-index="<?= $index ?>">
                        <?php endforeach; ?>
                        
                        <?php if (count($images) > 1): ?>
                            <button class="carousel-btn prev" onclick="moveCarousel(<?= $task['id'] ?>, -1); event.stopPropagation();">‹</button>
                            <button class="carousel-btn next" onclick="moveCarousel(<?= $task['id'] ?>, 1); event.stopPropagation();">›</button>
                            <div class="carousel-dots">
                                <?php foreach ($images as $index => $image): ?>
                                    <span class="dot <?= $index === 0 ? 'active' : '' ?>" 
                                          onclick="setCarouselImage(<?= $task['id'] ?>, <?= $index ?>); event.stopPropagation();"></span>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
                <div class="info-section">
                    <h2 class="task-title"><?= htmlspecialchars($task['task_title']) ?></h2>
                    <div class="task-user">
                        <a href="user.php?user=<?= urlencode($task['user']) ?>" class="user-profile-link">
                            <img src="<?= !empty($profilePhoto) ? '../docs/' . htmlspecialchars($profilePhoto) : '../docs/user_icon.png' ?>"
                                 alt="<?= htmlspecialchars($task['user']) ?>"
                                 class="task-user-avatar">
                            <span class="user-name"><?= htmlspecialchars($task['user']) ?></span>
                        </a>
                    </div>
                    <div class="task-description">
                        <?= nl2br(htmlspecialchars($task['description'])) ?>
                    </div>
                </div>
                <div class="delivery-section">
                    <div class="delivery-info">
                        <div class="delivery-time">
                            <span class="delivery-label">Delivery Time</span>
                            <span class="delivery-value"><?= htmlspecialchars($task['delivery_time']) ?></span>
                        </div>
                        <div class="delivery-price">
                            <span class="price-label">Price</span>
                            <span class="price-value"><?= number_format($task['pricing'], 2) ?>€</span>
                        </div>
                        <div class="delivery-category">
                            <span class="category-label">Category</span>
                            <span class="category-value"><?= htmlspecialchars($task['category']) ?></span>
                        </div>
                    </div>
                    
                    <?php if (isset($_SESSION['username'])): ?>
                    <div class="favorite-section">
                        <button class="favorite-button <?= $isFavorite ? 'favorited' : '' ?>" 
                                onclick="toggleFavorite(<?= $task['id'] ?>)" 
                                id="favoriteBtn">
                            <img src="../docs/star.png" alt="Star" class="favorite-star">
                            <span class="favorite-text"><?= $isFavorite ? 'Remove from favorites' : 'Add to favorites' ?></span>
                        </button>
                    </div>
                    <?php endif; ?>
                    
                    <div class="delivery-actions">
                        <?php if (!isset($_SESSION['username'])): ?>
                            <a href="#" class="message-button" onclick="alert('Please log in to message sellers'); return false;">Message</a>
                            <a href="#" class="hire-button" onclick="alert('Please log in to hire services'); return false;">Hire</a>
                        <?php elseif ($isTasker): ?>
                            <a href="#" class="message-button" onclick="alert('You are in Tasker mode. Switch to User mode in settings to hire services.'); return false;">Message</a>
                            <a href="#" class="hire-button" onclick="alert('You are in Tasker mode. Switch to User mode in settings to hire services.'); return false;">Hire</a>
                        <?php else: ?>
                            <a href="message_hub.php?user=<?= urlencode($task['user']) ?>&action=start_conversation" class="message-button">Message</a>
                            <a href="hire.php?id=<?= $task['id'] ?>" class="hire-button">Hire</a>
                        <?php endif; ?>
                    </div>
                </div>
                <div class="reviews-section">
                    <span class="review-label">Reviews</span>
                    <div class="review-rating-group">
                        <span class="review-rating-number">4.5</span>
                        <span class="review-stars">★★★★½☆</span>
                    </div>
                </div>
            </div>
        </div>
    </section>
</main>
</body>
</html>