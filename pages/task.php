<?php
session_start();
require_once(__DIR__ . '/../database/connection.php');
require_once(__DIR__ . '/../database/service_db.php');

$taskId = $_GET['id'] ?? null;

if (!$taskId) {
    header('Location: gigs.php');
    exit();
}

try {
    $db = getDatabaseConnection();
    
    // Get task with user details and media
    $stmt = $db->prepare("
        SELECT s.*, u.name as seller_name, u.profile_photo as seller_photo,
               (SELECT GROUP_CONCAT(file_path) FROM media WHERE service_id = s.id ORDER BY id ASC) as all_images
        FROM services s
        JOIN users u ON s.user = u.username
        WHERE s.id = ?
    ");
    $stmt->execute([$taskId]);
    $task = $stmt->fetch();
    
    if (!$task) {
        header('Location: gigs.php');
        exit();
    }
    
    $images = !empty($task['all_images']) ? explode(',', $task['all_images']) : ['default-gig.jpg'];
    
} catch (PDOException $e) {
    error_log("Error fetching task: " . $e->getMessage());
    header('Location: gigs.php');
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title><?= htmlspecialchars($task['taskname']) ?> | Taskly</title>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/task.css">
    <link rel="icon" href="../docs/logo.png" type="icon/x-icon">
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
                                 alt="<?= htmlspecialchars($task['taskname']) ?>" 
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
                    <h1 class="task-title"><?= htmlspecialchars($task['taskname']) ?></h1>
                    
                    <div class="task-user">
                        <a href="user.php?user=<?= urlencode($task['user']) ?>" class="user-profile-link">
                            <img src="<?= !empty($task['seller_photo']) ? '../docs/' . htmlspecialchars($task['seller_photo']) : '../docs/user_icon.png' ?>" 
                                 alt="<?= htmlspecialchars($task['seller_name']) ?>" 
                                 class="task-user-avatar">
                            <span class="user-name"><?= htmlspecialchars($task['seller_name']) ?></span>
                        </a>
                    </div>
                    
                    <div class="task-description">
                        <p><?= htmlspecialchars($task['description']) ?></p>
                    </div>
                </div>
                
                <div class="delivery-section">
                    <h3>Order Details</h3>
                    <p><strong>Delivery Time:</strong> <?= htmlspecialchars($task['delivery_time']) ?></p>
                    <p><strong>Category:</strong> <?= htmlspecialchars($task['category']) ?></p>
                    <p><strong>Price:</strong> €<?= htmlspecialchars($task['pricing']) ?></p>
                    
                    <div class="delivery-actions">
                        <button class="message-button">Message Seller</button>
                        <button class="hire-button">Hire Now</button>
                    </div>
                </div>
                
                <div class="reviews-section">
                    <h3>Reviews</h3>
                    <div class="review-rating-group">
                        <span class="review-label">Rating:</span>
                        <span class="review-rating-number"><?= htmlspecialchars($task['rating']) ?></span>
                        <span class="review-stars">★★★★★</span>
                        <span>(<?= htmlspecialchars($task['Nrating']) ?> reviews)</span>
                    </div>
                </div>
            </div>
        </div>
    </section>
</main>
</body>
</html>