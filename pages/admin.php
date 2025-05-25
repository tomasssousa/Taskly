<?php
session_start();

if (!isset($_SESSION['username'])) {
    header('Location: login.php');
    exit();
}

require_once(__DIR__ . '/../database/service_db.php');
require_once(__DIR__ . '/../database/user_db.php');
require_once(__DIR__ . '/../database/connection.php');

$currentUser = User::get_user($_SESSION['username']);

if (!$currentUser || !$currentUser['admin_power']) {
    header('Location: home.php');
    exit();
}

$allUsers = [];
$allServices = [];
$totalUsers = 0;
$totalServices = 0;

$allUsers = User::get_users();
$totalUsers = count($allUsers);

try {
    $db = Database::getInstance();
    $stmt = $db->prepare("
        SELECT s.*, u.name as seller_name 
        FROM services s 
        LEFT JOIN users u ON s.user = u.username 
        ORDER BY s.id DESC
    ");
    $stmt->execute();
    $allServices = $stmt->fetchAll();
    $totalServices = count($allServices);
} catch (Exception $e) {
    error_log("Error fetching services: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>Admin Dashboard | Taskly</title>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/admin.css">
    <link rel="icon" href="../docs/logo.png" type="icon/x-icon">
    <script src="js/admin.js" defer></script>
</head>
<body>
<?php include_once(__DIR__ . '/../components/header.php'); ?>

<main class="admin-layout">
    <h1>Admin Dashboard</h1>

    <div class="admin-content">
        <h2>User Management (<?= count($allUsers) ?> users)</h2>
        <div class="admin-table-container">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>Username</th>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Admin</th>
                        <th>Task Mode</th>
                        <th>Money</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($allUsers)): ?>
                        <?php foreach ($allUsers as $user): ?>
                            <tr>
                                <td><?= htmlspecialchars($user['username']) ?></td>
                                <td><?= htmlspecialchars($user['name']) ?></td>
                                <td><?= htmlspecialchars($user['email']) ?></td>
                                <td><?= $user['admin_power'] ? 'Yes' : 'No' ?></td>
                                <td><?= $user['task_mode'] ? 'Tasker' : 'User' ?></td>
                                <td><?= htmlspecialchars($user['money']) ?></td>
                                <td>
                                    <a href="user.php?user=<?= urlencode($user['username']) ?>" class="admin-btn view-btn">View</a>
                                    <?php if (!$user['admin_power']): ?>
                                        <a href="#" class="admin-btn promote-btn" onclick="promoteUser('<?= htmlspecialchars($user['username']) ?>'); return false;">Admin</a>
                                    <?php endif; ?>
                                    <?php if ($user['username'] !== $_SESSION['username']): ?>
                                        <a href="#" class="admin-btn delete-btn" onclick="deleteUser('<?= htmlspecialchars($user['username']) ?>'); return false;">Delete</a>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr><td colspan="7">No users found.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <h2>Category Management</h2>
        <div class="category-management">
            <div class="add-category-form">
                <h3>Add New Category</h3>
                <form id="addCategoryForm" onsubmit="addCategory(event)">
                    <div class="form-group">
                        <input type="text" 
                               id="categoryName" 
                               name="categoryName" 
                               placeholder="Category Name" 
                               required>
                        <button type="submit" class="admin-btn add-btn">Add</button>
                    </div>
                </form>
            </div>

            <div class="current-categories">
                <h3>Current Categories</h3>
                <div class="category-list">
                    <?php 
                    $categories = Service::get_categories();
                    
                    foreach ($categories as $category): ?>
                        <div class="category-item" data-category="<?= htmlspecialchars($category) ?>">
                            <span class="category-name"><?= htmlspecialchars($category) ?></span>
                            <button class="admin-btn delete-btn small" onclick="deleteCategory('<?= htmlspecialchars($category) ?>')">Delete</button>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>

        <h2>Task Management (<?= count($allServices) ?> services)</h2>
        <div class="admin-table-container">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Task Name</th>
                        <th>Seller</th>
                        <th>Category</th>
                        <th>Price</th>
                        <th>Rating</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($allServices)): ?>
                        <?php foreach ($allServices as $service): ?>
                            <tr>
                                <td><?= $service['id'] ?></td>
                                <td><?= htmlspecialchars($service['taskname']) ?></td>
                                <td><?= htmlspecialchars($service['seller_name'] ?? $service['user']) ?></td>
                                <td><?= htmlspecialchars($service['category']) ?></td>
                                <td>€<?= $service['pricing'] ?></td>
                                <td><?= $service['rating'] ?> (<?= $service['Nrating'] ?>)</td>
                                <td>
                                    <a href="task.php?id=<?= $service['id'] ?>" class="admin-btn view-btn">View</a>
                                    <a href="#" class="admin-btn delete-btn" onclick="deleteService(<?= $service['id'] ?>); return false;">Delete</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr><td colspan="7">No services found.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</main>
</body>
</html>