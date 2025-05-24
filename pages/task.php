<?php
session_start();

require_once(__DIR__ . '/../database/service_db.php');
require_once(__DIR__ . '/../database/user_db.php');

// Get task ID from URL
$taskId = $_GET['id'] ?? null;

if ($taskId) {
    try {
        
        $task = Service::get_service_with_media($taskId);
        
        // Convert image string to array
        $images = !empty($task['all_images']) ? explode(',', $task['all_images']) : ['default-gig.jpg'];

        // After fetching $task
       
        $userInfo = User::get_user($task['user']);
        $profilePhoto = $userInfo && !empty($userInfo['profile_photo']) ? $userInfo['profile_photo'] : null;

    } catch (Exception $e) {
        die("Error: " . $e->getMessage());
    }
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
                    <!-- Delivery details will go here -->
                    <div class="delivery-actions">
                        <a href="message_hub.php?user=<?= urlencode($task['user']) ?>&action=start_conversation" class="message-button">Message</a>
                        <button class="hire-button">Hire</button>
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