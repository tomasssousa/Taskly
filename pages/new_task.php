<?php 
session_start();
require_once(__DIR__ . '/../database/service_db.php');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <title>Taskly | The website that solves your problems</title>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/new_task.css">
    <link rel="icon" href="../docs/logo.png" type="icon/x-icon">
</head>
<body>
<?php include_once(__DIR__ . '/../components/header.php'); ?>
    <main>
        <div class="container">
            <h2>Create a Task</h2>
            <?php if (isset($_SESSION['error'])): ?>
                <p class="error"><?= htmlspecialchars($_SESSION['error']) ?></p>
                <?php unset($_SESSION['error']); ?>
            <?php endif; ?>
            <form action="../actions/action_newtask.php" method="post" enctype="multipart/form-data">
                <input type="text" placeholder="Task Title" name="taskname" required>
                <textarea placeholder="Task Description" name="taskdesc" rows="4" required></textarea>
                <input type="text" placeholder="Pricing" name="pricing" min="1" step="0.01" required>
                <select name="deltime" required>
                    <option value="" disabled selected>Select Delivery Time</option>
                    <option value="less_than_1_day">Less than a day</option>
                    <option value="1_2_days">1-2 days</option>
                    <option value="less_than_a_week">Less than a week</option>
                    <option value="more_than_a_week">More than a week</option>
                </select>
                <select name="category" required>
                    <option value="" disabled selected>Select Category</option>
                    <?php 
                    $categories = Service::get_categories();
                    foreach ($categories as $category): ?>
                        <option value="<?= htmlspecialchars($category) ?>">
                            <?= htmlspecialchars($category) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <input type="file" placeholder="Upload photo(s)" name="taskphoto[]" accept="image/*" multiple>
                <button type="submit">Submit Task</button>
            </form>
        </div>
    </main>
</body>
</html>