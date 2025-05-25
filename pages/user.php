<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();

require_once(__DIR__ . '/../database/connection.php');
require_once(__DIR__ . '/../database/user_db.php');
require_once(__DIR__ . '/../database/service_db.php');

try {
    $db = Database::getInstance();
    if (!$db) {
        throw new Exception("Database connection failed");
    }
} catch (Exception $e) {
    die("Error: " . $e->getMessage());
}

$profileUser = $_GET['user'] ?? $_SESSION['username'] ?? null;

$userData = null;
$services = [];
if ($profileUser) {
    $userData = User::get_user($profileUser);
    try {
        $services = Service::get_services_by_user($profileUser);
    } catch (PDOException $e) {
        error_log("Error fetching services: " . $e->getMessage());
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <title>Taskly | The website that solves your problems</title>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/user.css">
    <link rel="stylesheet" href="css/gig_card.css">
    <link rel="icon" href="../docs/logo.png" type="icon/x-icon">
    <script src="js/gig_card.js" defer></script>
    <script src="js/user.js" defer></script>
</head>
<body>
<?php include_once(__DIR__ . '/../components/header.php'); ?>

<main class="main-layout">
    <aside class="user">
        <?php if (isset($_SESSION['username']) && $_SESSION['username'] === $profileUser): ?>
            <div class="edit-icon-container">
                <a href="javascript:void(0);" class="edit-icon" title="Edit Profile" id="editButton" onclick="toggleEditProfile()">
                    <img src="../docs/edit.png" alt="Edit Profile" class="edit-icon-img" id="editButtonImg">
                </a>
                <div class="button-wrapper">
                    <a class="save-button" href="#" id="saveButton" style="display: none;" onclick="saveProfileChanges()">Save</a>
                </div>                
            </div>
        <?php endif; ?>    

        <div class="user-profile" id="userProfile">
            <div class="user-photo-container" id="userPhotoContainer">
                <img id="userProfilePic" 
                    src="<?= !empty($userData['profile_photo']) ? '../docs/' . htmlspecialchars($userData['profile_photo']) : '../docs/user_icon.png' ?>" 
                    alt="User Profile Picture" class="user-photo">
                <div class="photo-overlay">
                    <img src="../docs/edit_photo.png" alt="Edit Icon" class="overlay-icon">
                </div>
                <input type="file" id="profileImage" class="file-input" onchange="previewImage(event)">
            </div>

            <h2 class="user-name" id="userName">
                <?= htmlspecialchars($userData['name'] ?? $_SESSION['username'] ?? 'Guest') ?>
            </h2>
            <input type="text" id="editName" 
                value="<?= htmlspecialchars($userData['name'] ?? $_SESSION['username'] ?? 'Guest') ?>" 
                class="user-name-input" style="display: none;" onblur="updateName()">

            <p class="user-bio" id="userBio" onclick="editBio()">
                <?= htmlspecialchars($userData['bio'] ?? 'Full-stack developer passionate about clean code and user-centered design.') ?>
            </p>
            <textarea id="editBio" class="bio-input" style="display: none;" 
                    oninput="autoResize(this)" onblur="updateBio()"><?= htmlspecialchars($userData['bio'] ?? 'Full-stack developer passionate about clean code and user-centered design.') ?></textarea>

            <div class="user-skills">
                <h3>Skills</h3>
                <ul class="skills-list">
                    <?php
                    if (!empty($userData['skills'])) {
                        $skillsArray = explode(',', $userData['skills']);
                        foreach ($skillsArray as $skill) {
                            echo '<li>' . htmlspecialchars(trim($skill)) . '</li>';
                        }
                    } else {
                        echo '<li>JavaScript</li><li>React</li><li>UI/UX</li>';
                    }
                    ?>
                </ul>

                <div class="skill-input-wrapper" id="addSkillContainer" style="display: none;">
                    <div class="skill-input-container">
                        <input type="text" 
                               placeholder="Add a skill..." 
                               class="add-skill-input" 
                               id="addSkillInput"
                               onkeypress="if(event.key === 'Enter') addSkill()">
                        <button type="button" class="add-skill-button" onclick="addSkill()">+</button>
                    </div>
                </div>
            </div>
        </div>
    </aside>

    <section class="content-area">
        <h1 class="page-title">My Tasks</h1>
        <div class="gigs-container">
            <?php
            require_once(__DIR__ . '/gig_card.php');
            if (!empty($services)) {
                foreach($services as $service) {
                    renderGigCard($service, true); 
                }
            } else {
                echo '<p class="no-tasks">No tasks found. <a href="new_task.php">Create your first task!</a></p>';
            }
            ?>
        </div>
    </section>
</main>
</body>
</html>

