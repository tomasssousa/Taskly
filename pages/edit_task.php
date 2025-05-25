<?php
session_start();

require_once(__DIR__ . '/../database/service_db.php');
require_once(__DIR__ . '/../database/user_db.php');

// Check if user is logged in and in tasker mode
if (!isset($_SESSION['username'])) {
    header('Location: login.php');
    exit();
}

$currentUser = User::get_user($_SESSION['username']);
if (!$currentUser || !$currentUser['task_mode']) {
    $_SESSION['error'] = 'You must be in Tasker mode to edit tasks.';
    header('Location: settings.php');
    exit();
}

// Get task ID from URL
$taskId = $_GET['id'] ?? null;
if (!$taskId) {
    header('Location: home.php');
    exit();
}

try {
    $task = Service::get_service_by_id_and_user($taskId, $_SESSION['username']);
    
    if (!$task) {
        $_SESSION['error'] = 'Task not found or you do not have permission to edit this task.';
        header('Location: home.php');
        exit();
    }
    
    // Get existing images from the raw database column
    $existingImages = [];
    if (!empty($task['image'])) {
        $imageList = explode(',', $task['image']);
        foreach ($imageList as $img) {
            $img = trim($img);
            if (!empty($img)) {
                $existingImages[] = $img;
            }
        }
    }

} catch (Exception $e) {
    $_SESSION['error'] = 'Error loading task: ' . $e->getMessage();
    header('Location: home.php');
    exit();

}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <title>Edit Task | Taskly</title>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/new_task.css">
    <link rel="stylesheet" href="css/edit_task.css">
    <link rel="icon" href="../docs/logo.png" type="icon/x-icon">
</head>
<body>
<?php include_once(__DIR__ . '/../components/header.php'); ?>
    <main>
        <div class="container">
            <h2>Edit Task</h2>
            
            <?php if (isset($_SESSION['error'])): ?>
                <p class="error"><?= htmlspecialchars($_SESSION['error']) ?></p>
                <?php unset($_SESSION['error']); ?>
            <?php endif; ?>
            
            <?php if (isset($_SESSION['success'])): ?>
                <p class="success"><?= htmlspecialchars($_SESSION['success']) ?></p>
                <?php unset($_SESSION['success']); ?>
            <?php endif; ?>
            
            <form action="../actions/action_edit_task.php" method="post" enctype="multipart/form-data">
                <input type="hidden" name="task_id" value="<?= htmlspecialchars($task['id']) ?>">
                
                <input type="text" 
                       placeholder="Task Title" 
                       name="taskname" 
                       value="<?= htmlspecialchars($task['taskname'] ?? $task['task_title'] ?? '') ?>" 
                       required>
                
                <textarea placeholder="Task Description" 
                         name="taskdesc" 
                         rows="4" 
                         required><?= htmlspecialchars($task['description']) ?></textarea>
                
                <input type="number" 
                       placeholder="Pricing" 
                       name="pricing" 
                       value="<?= htmlspecialchars($task['pricing']) ?>" 
                       min="1" 
                       step="0.01" 
                       required>
                
                <select name="deltime" required>
                    <option value="" disabled>Select Delivery Time</option>
                    <option value="less_than_1_day" <?= $task['delivery_time'] === 'less_than_1_day' ? 'selected' : '' ?>>Less than a day</option>
                    <option value="1_2_days" <?= $task['delivery_time'] === '1_2_days' ? 'selected' : '' ?>>1-2 days</option>
                    <option value="less_than_a_week" <?= $task['delivery_time'] === 'less_than_a_week' ? 'selected' : '' ?>>Less than a week</option>
                    <option value="more_than_a_week" <?= $task['delivery_time'] === 'more_than_a_week' ? 'selected' : '' ?>>More than a week</option>
                </select>
                
                <select name="category" required>
                    <option value="" disabled>Select Category</option>
                    <?php 
                    $categories = Service::get_categories();
                    foreach ($categories as $category): ?>
                        <option value="<?= htmlspecialchars($category) ?>" <?= $task['category'] === $category ? 'selected' : '' ?>>
                            <?= htmlspecialchars(str_replace('_', ' & ', $category)) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                
                <?php if (!empty($existingImages)): ?>
                <div class="existing-images">
                    <label>Current Images (<?= count($existingImages) ?>):</label>
                    <div class="image-grid">
                        <?php foreach ($existingImages as $index => $image): ?>
                            <div class="image-item">
                                <img src="../docs/<?= htmlspecialchars($image) ?>" alt="Task Image" class="preview-image">
                                <label>
                                    <input type="checkbox" name="remove_images[]" value="<?= htmlspecialchars($image) ?>">
                                    Remove
                                </label>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
                <?php endif; ?>
                
                <div class="file-input-container">
                    <label for="taskphoto">Upload new photos (optional):</label>
                    <input type="file" id="taskphoto" name="taskphoto[]" accept="image/*" multiple>
                </div>
                
                <div class="button-group">
                    <button type="button" class="cancel-btn" onclick="window.location.href='home.php'">Cancel</button>
                    <button type="submit" class="submit-btn">Update Task</button>
                </div>
            </form>
        </div>
    </main>
</body>
</html>