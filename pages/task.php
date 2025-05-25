<?php
session_start();

require_once(__DIR__ . '/../database/service_db.php');
require_once(__DIR__ . '/../database/user_db.php');

$taskId = $_GET['id'] ?? null;

if (!$taskId) {
    header('Location: home.php');
    exit();
}

$currentUser = null;
$isTasker = false;
$isFavorite = false;
$task = null;

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

if (isset($_SESSION['username'])) {
    $currentUser = User::get_user($_SESSION['username']);
    $isTasker = $currentUser && $currentUser['task_mode'];
    
    try {
        $isFavorite = Service::get_favourite_count($_SESSION['username'], $taskId);
    } catch (Exception $e) {
        error_log("Favorites check error: " . $e->getMessage());
        $isFavorite = false;
    }
} else {
    $isFavorite = false;
}

$reviews = Service::get_service_reviews($taskId);

function formatDeliveryTime($value) {
    // Special case for "1_2_days"
    if ($value === '1_2_days') return '1 to 2 days';
    // Replace underscores with spaces and hyphens with "to"
    $value = str_replace('_', ' ', $value);
    $value = str_replace('-', 'to', $value);
    // Capitalize first letter
    return ucfirst($value);
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
                
                <div class="delivery-section">
                    <div class="delivery-info">
                        <div class="delivery-time">
                            <span class="delivery-label">Delivery Time</span>
                            <span class="delivery-value"><?= formatDeliveryTime($task['delivery_time']) ?></span>
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
                
                <div class="reviews-section">
    <div class="reviews-header">
        <span>Reviews</span>
        <?php 
        $rating = Service::get_service_rating($taskId);
        if ($rating['count'] > 0) {
            echo number_format($rating['average'], 2) . " ⭐ (" . $rating['count'] . ")";
        }
        ?>
    </div>
    <div class="reviews-list">
        <?php if (!empty($reviews)): ?>
            <?php foreach ($reviews as $review): ?>
                <div class="review-item">
                    <div class="review-user">
                        <img src="<?= !empty($review['profile_photo']) ? '../docs/' . htmlspecialchars($review['profile_photo']) : '../docs/user_icon.png' ?>" 
                             alt="<?= htmlspecialchars($review['user_name']) ?>" 
                             class="review-avatar">
                        <span class="review-username"><?= htmlspecialchars($review['user_name']) ?></span>
                        <?php if ($review['transaction_rating']): ?>
                            <span class="review-rating">
                                <?php for ($i = 1; $i <= 5; $i++): ?>
                                    <span class="<?= $i <= $review['transaction_rating'] ? 'star filled' : 'star empty' ?>">★</span>
                                <?php endfor; ?>
                            </span>
                        <?php endif; ?>
                    </div>
                    <div class="review-text">
                        <?= nl2br(htmlspecialchars($review['text'])) ?>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="no-reviews">
                No reviews yet. Be the first to review this service!
            </div>
        <?php endif; ?>
    </div>
    <?php if (isset($_SESSION['username']) && $_SESSION['username'] !== $task['user']): ?>
        <form id="reviewForm" class="review-form" onsubmit="submitReview(event)">
            <div class="rating-input">
                <label>Rating:</label>
                <?php for ($i = 1; $i <= 5; $i++): ?>
                    <span class="star-input" data-rating="<?= $i ?>" onclick="setRating(<?= $i ?>)">★</span>
                <?php endfor; ?>
                <input type="hidden" id="ratingValue" name="rating" value="0" required>
            </div>
            <textarea id="reviewText" name="reviewText" placeholder="Write your review here..." rows="2" required></textarea>
            <button type="submit" class="submit-review-btn">Submit Review</button>
        </form>
    <?php elseif (!isset($_SESSION['username'])): ?>
        <div class="review-form disabled">Please <a href="login.php">log in</a> to leave a review</div>
    <?php else: ?>
        <div class="review-form disabled">You cannot review your own service</div>
    <?php endif; ?>
</div>
            </div> 
        </div>
    </section>
</main>
</body>
</html>