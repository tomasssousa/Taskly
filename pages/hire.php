<?php
session_start();

require_once(__DIR__ . '/../database/service_db.php');
require_once(__DIR__ . '/../database/user_db.php');

// Check if user is logged in
if (!isset($_SESSION['username'])) {
    header('Location: login.php');
    exit();
}

// Check if user is in user mode (not tasker mode)
$currentUser = User::get_user($_SESSION['username']);
if ($currentUser && $currentUser['task_mode']) {
    $_SESSION['error'] = 'You are in Tasker mode. Switch to User mode in settings to hire services.';
    header('Location: home.php');
    exit();
}

// Get service ID from URL
$serviceId = $_GET['id'] ?? null;
if (!$serviceId) {
    header('Location: home.php');
    exit();
}

try {
    $service = Service::get_service_with_media($serviceId);
    if (!$service) {
        header('Location: home.php');
        exit();
    }
} catch (Exception $e) {
    die("Error: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <title>Hire Service | Taskly</title>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/hire.css">
    <link rel="icon" href="../docs/logo.png" type="icon/x-icon">
    <script src="js/hire.js"></script>
</head>
<body>
<?php include_once(__DIR__ . '/../components/header.php'); ?>

<main class="main-layout">
    <div class="hire-container">
        <h2>Hire Tasker</h2>
        
        <div class="service-summary">
            <h3><?= htmlspecialchars($service['task_title']) ?></h3>
            <p class="service-seller">by <?= htmlspecialchars($service['user']) ?></p>
            <p class="service-delivery">Delivery: <?= htmlspecialchars($service['delivery_time']) ?></p>
            <p class="service-price">Price: €<?= number_format($service['pricing'], 2) ?></p>
        </div>

        <form id="hireForm" class="hire-form">
            <input type="hidden" id="serviceId" value="<?= $service['id'] ?>">
            
            <div class="form-group">
                <label for="phone">Phone Number *</label>
                <div class="phone-input-container">
                    <img src="../docs/mbway.png" class="phone-logo">
                    <input type="tel" id="phone" name="phone" required placeholder="+351 123 456 789">
                </div>
            </div>
            
            <div class="form-group">
                <label for="details">Additional Details (optional)</label>
                <textarea id="details" name="details" rows="4" placeholder="Any specific requirements or details..."></textarea>
            </div>
            
            <button type="submit" class="pay-button">
                <span class="button-text">Pay Now</span>
                <div class="loading-spinner" style="display: none;"></div>
            </button>
        </form>
        
        <div id="successMessage" class="success-message" style="display: none;">
            <h3>Payment Accepted!</h3>
            <p>Your order has been placed successfully. The seller will contact you soon.</p>
            <a href="home.php" class="back-button">Back to Home</a>
        </div>
    </div>
</main>

</body>
</html>