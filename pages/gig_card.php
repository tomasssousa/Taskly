<script src="js/gig_card.js" defer></script>
<link rel="stylesheet" href="css/gig_card.css">


<?php
function renderGigCard($service, $showSettings = false) {
    $images = [];
    if (!empty($service['all_images'])) {
        $imageList = explode(',', $service['all_images']);
        foreach ($imageList as $image) {
            $image = trim($image);
            if (!empty($image) && file_exists(__DIR__ . '/../docs/' . $image)) {
                $images[] = $image;
            }
        }
    }
    
    if (empty($images)) {
        $images = ['default-gig.jpg'];
    }
    
    $isOwner = isset($_SESSION['username']) && $_SESSION['username'] === $service['user'];
    ?>
    <div class="gig-card" data-gig-id="<?= $service['id'] ?>">
        <?php if ($isOwner && $showSettings): ?>
            <div class="gig-actions">
                <button class="gig-settings-btn" data-gig-id="<?= $service['id'] ?>" onclick="toggleGigMenu(<?= $service['id'] ?>)">
                    <img src="../docs/settings.png" alt="Settings" class="settings-icon">
                </button>
                <div class="gig-menu" id="gigMenu_<?= $service['id'] ?>" style="display: none;">
                    <a href="edit_task.php?id=<?= $service['id'] ?>" class="menu-item">Edit Task</a>
                    <a href="#" class="menu-item delete-task" onclick="deleteTask(<?= $service['id'] ?>)">Delete Task</a>
                </div>
            </div>
        <?php endif; ?>
        <div class="gig-image-container">
            <?php foreach ($images as $index => $image): ?>
                <img src="../docs/<?= htmlspecialchars($image) ?>" 
                        alt="<?= htmlspecialchars($service['task_title']) ?>" 
                        class="gig-image <?= $index === 0 ? 'active' : '' ?>"
                        data-index="<?= $index ?>">
            <?php endforeach; ?>
            
        </div>
        <div class="gig-details">
            <h3><?= htmlspecialchars($service['task_title']) ?></h3>
            <div class="gig-seller">
                <a href="user.php?user=<?= urlencode($service['user']) ?>" class="gig-seller-link">
                    <img src="<?= !empty($service['seller_photo']) ? '../docs/' . htmlspecialchars($service['seller_photo']) : '../docs/user_icon.png' ?>" 
                         alt="<?= htmlspecialchars($service['seller_name']) ?>" 
                         class="seller-avatar">
                    <span><?= htmlspecialchars($service['seller_name']) ?></span>
                </a>
            </div>
            <p class="gig-description"><?= htmlspecialchars($service['description']) ?></p>
            <div class="gig-pricing">
                <span class="delivery"><?= htmlspecialchars($service['delivery_time']) ?> delivery</span>
            </div>
            <?php if (isset($service['status'])): ?>
                <div class="gig-status">
                    Status: <span class="status-<?= htmlspecialchars(strtolower($service['status'])) ?>">
                        <?= htmlspecialchars($service['status']) ?>
                    </span>
                </div>
            <?php endif; ?>
            <span class="gig-price">€<?= htmlspecialchars($service['pricing']) ?></span>
        </div>
    </div>
    <?php
}
?>
