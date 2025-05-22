<?php
function renderGigCard($service) {
    $images = !empty($service['all_images']) ? explode(',', $service['all_images']) : ['default-gig.jpg'];
    $isOwner = isset($_SESSION['username']) && $_SESSION['username'] === $service['user'];
    ?>
    <div class="gig-card" data-gig-id="<?= $service['id'] ?>">
        <!-- Only render gig-actions if user is owner -->
        <?php if ($isOwner): ?>
            <div class="gig-actions">
                <button class="gig-settings-btn" onclick="toggleGigMenu(<?= $service['id'] ?>)">
                    <img src="../docs/settings.png" alt="Settings" class="settings-icon">
                </button>
                <div class="gig-menu" id="gigMenu_<?= $service['id'] ?>" style="display: none;">
                    <a href="edit_task.php?id=<?= $service['id'] ?>">Edit Task</a>
                    <a href="#" onclick="showDeleteConfirmation(<?= $service['id'] ?>)">Delete Task</a>
                </div>
            </div>
        <?php endif; ?>
        <div class="gig-image-container">
            <div class="carousel" data-gig-id="<?= $service['id'] ?>">
                <?php foreach ($images as $index => $image): ?>
                    <img src="../docs/<?= htmlspecialchars($image) ?>" 
                         alt="<?= htmlspecialchars($service['task_title']) ?>" 
                         class="gig-image <?= $index === 0 ? 'active' : '' ?>"
                         data-index="<?= $index ?>">
                <?php endforeach; ?>
                
                <?php if (count($images) > 1): ?>
                    <button class="carousel-btn prev">‹</button>
                    <button class="carousel-btn next">›</button>
                    <div class="carousel-dots">
                        <?php foreach ($images as $index => $image): ?>
                            <span class="dot <?= $index === 0 ? 'active' : '' ?>" 
                                  onclick="setCarouselImage(<?= $service['id'] ?>, <?= $index ?>)"></span>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
        <div class="gig-details">
            <h3><?= htmlspecialchars($service['task_title']) ?></h3>
            <div class="gig-seller">
                <img src="<?= !empty($service['seller_photo']) ? '../docs/' . htmlspecialchars($service['seller_photo']) : '../docs/user_icon.png' ?>" 
                     alt="<?= htmlspecialchars($service['seller_name']) ?>" 
                     class="seller-avatar">
                <span><?= htmlspecialchars($service['seller_name']) ?></span>
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


<script>
document.addEventListener('DOMContentLoaded', function() {
    initializeCarousels();
});

function initializeCarousels() {
    document.querySelectorAll('.carousel-btn').forEach(button => {
        button.addEventListener('click', function(e) {
            e.preventDefault();
            const carousel = this.closest('.carousel');
            const direction = this.classList.contains('prev') ? -1 : 1;
            const gigId = carousel.dataset.gigId;
            moveCarousel(gigId, direction);
        });
    });
}

function moveCarousel(gigId, direction) {
    const carousel = document.querySelector(`.carousel[data-gig-id="${gigId}"]`);
    const images = carousel.querySelectorAll('.gig-image');
    const dots = carousel.querySelectorAll('.dot');
    
    let currentIndex = Array.from(images).findIndex(img => img.classList.contains('active'));
    images[currentIndex].classList.remove('active');
    dots[currentIndex].classList.remove('active');
    
    currentIndex = (currentIndex + direction + images.length) % images.length;
    
    images[currentIndex].classList.add('active');
    dots[currentIndex].classList.add('active');
}

function setCarouselImage(gigId, index) {
    const carousel = document.querySelector(`.carousel[data-gig-id="${gigId}"]`);
    const images = carousel.querySelectorAll('.gig-image');
    const dots = carousel.querySelectorAll('.dot');
    
    carousel.querySelector('.gig-image.active').classList.remove('active');
    carousel.querySelector('.dot.active').classList.remove('active');
    
    images[index].classList.add('active');
    dots[index].classList.add('active');
}

function toggleGigMenu(gigId) {
    const menu = document.getElementById(`gigMenu_${gigId}`);
    const allMenus = document.querySelectorAll('.gig-menu');
    
    // Close all other menus
    allMenus.forEach(m => {
        if (m.id !== `gigMenu_${gigId}`) {
            m.style.display = 'none';
        }
    });
    
    menu.style.display = menu.style.display === 'none' ? 'block' : 'none';
}

// Close menus when clicking outside
document.addEventListener('click', function(event) {
    if (!event.target.closest('.gig-actions')) {
        document.querySelectorAll('.gig-menu').forEach(menu => {
            menu.style.display = 'none';
        });
    }
});

function showDeleteConfirmation(taskId) {
    showPopup('deleteTaskPopup', () => deleteTask(taskId));
}

function deleteTask(taskId) {
    fetch(`../actions/action_delete_task.php?id=${taskId}`, {
        method: 'POST',
        credentials: 'same-origin'
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            const gigCard = document.querySelector(`[data-gig-id="${taskId}"]`).closest('.gig-card');
            gigCard.remove();
        } else {
            throw new Error(data.error || 'Failed to delete task');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Failed to delete task: ' + error.message);
    });
}
</script>

<?php 
require_once(__DIR__ . '/../components/warning_popup.php');
showWarningPopup(
    'deleteTaskPopup', 
    'Delete Task?', 
    'Are you sure you want to delete this task? This cannot be undone.',
    'Delete',
    'Cancel'
); 
?>
