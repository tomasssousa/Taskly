<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();

require_once(__DIR__ . '/../database/connection.php');

try {
    $db = getDatabaseConnection();
    if (!$db) {
        throw new Exception("Database connection failed");
    }
} catch (Exception $e) {
    die("Error: " . $e->getMessage());
}

$profileUser = $_GET['user'] ?? $_SESSION['username'] ?? null;

$userData = null;
if ($profileUser) {
    $stmt = $db->prepare("SELECT profile_photo, name, bio, skills FROM users WHERE username = ?");
    $stmt->execute([$profileUser]);
    $userData = $stmt->fetch();
    
}
?>


<!DOCTYPE html>
<html lang="en">
    <head>
        <title>Taskly | The website that solves your problems</title>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <link rel="stylesheet" href="style.css">
        <link rel="stylesheet" href="user.css">
        <link rel="stylesheet" href="gig_card.css">
        <link rel="icon" href="../docs/logo.png" type="icon/x-icon">
    </head>
    <body>
    <?php include_once(__DIR__ . '/../components/header.php'); ?>

    <?php
    // Get the user from the URL or session
    $profileUser = $_GET['user'] ?? $_SESSION['username'] ?? null;
    ?>

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
                <!-- Profile Photo -->
                <div class="user-photo-container" id="userPhotoContainer">
                    <img id="userProfilePic" 
                        src="<?= !empty($userData['profile_photo']) ? '../docs/' . htmlspecialchars($userData['profile_photo']) : '../docs/user_icon.png' ?>" 
                        alt="User Profile Picture" class="user-photo">
                    <div class="photo-overlay">
                        <img src="../docs/edit_photo.png" alt="Edit Icon" class="overlay-icon">
                    </div>
                    <input type="file" id="profileImage" class="file-input" onchange="previewImage(event)">
                </div>

                <!-- Name Edit -->
                <h2 class="user-name" id="userName">
                    <?= htmlspecialchars($userData['name'] ?? $_SESSION['username'] ?? 'Guest') ?>
                </h2>
                <input type="text" id="editName" 
                    value="<?= htmlspecialchars($userData['name'] ?? $_SESSION['username'] ?? 'Guest') ?>" 
                    class="user-name-input" style="display: none;" onblur="updateName()">

                <!-- Bio Edit -->
                <p class="user-bio" id="userBio" onclick="editBio()">
                    <?= htmlspecialchars($userData['bio'] ?? 'Full-stack developer passionate about clean code and user-centered design.') ?>
                </p>
                <textarea id="editBio" class="bio-input" style="display: none;" 
                        oninput="autoResize(this)" onblur="updateBio()"><?= htmlspecialchars($userData['bio'] ?? 'Full-stack developer passionate about clean code and user-centered design.') ?></textarea>

                <!-- Skills Section -->
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
                </div> <!-- .user-skills -->
            </div> <!-- .user-profile -->

        </aside>

        <section class="content-area">
            <h1 class="page-title">My Tasks</h1>

            <div class="gigs-container">
                <?php
                // Fetch user's services/tasks
                try {
                    $stmt = $db->prepare("
                        SELECT s.*, u.profile_photo as seller_photo, u.name as seller_name,
                               COALESCE(s.taskname, s.description) as task_title,
                               (SELECT COUNT(*) FROM transactions WHERE service_id = s.id) as order_count,
                               (SELECT GROUP_CONCAT(file_path) FROM media WHERE service_id = s.id ORDER BY id ASC) as all_images
                        FROM services s
                        JOIN users u ON s.user = u.username
                        WHERE s.user = ?
                        ORDER BY s.id DESC
                    ");
                    $stmt->execute([$profileUser]);
                    $services = $stmt->fetchAll();

                    require_once(__DIR__ . '/gig_card.php');
                    
                    if (!empty($services)) {
                        foreach($services as $service) {
                            renderGigCard($service);
                        }
                    } else {
                        echo '<p class="no-tasks">No tasks found. <a href="new_task.php">Create your first task!</a></p>';
                    }
                    
                } catch (PDOException $e) {
                    error_log("Error fetching services: " . $e->getMessage());
                    echo '<p class="error">Error loading tasks. Please try again later.</p>';
                }
                ?>
            </div>
        </section>
    </main>

    <script>
    function toggleEditProfile() {
        const editMode = document.getElementById('editName').style.display === 'none';

        document.getElementById('userName').style.display = editMode ? 'none' : 'block';
        document.getElementById('editName').style.display = editMode ? 'block' : 'none';

        document.getElementById('userBio').style.display = editMode ? 'none' : 'block';
        document.getElementById('editBio').style.display = editMode ? 'block' : 'none';

        document.getElementById('addSkillContainer').style.display = editMode ? 'block' : 'none';
        document.getElementById('userPhotoContainer').style.cursor = editMode ? 'pointer' : 'default';

        document.getElementById('editButton').style.display = editMode ? 'none' : 'block';
        document.getElementById('saveButton').style.display = editMode ? 'block' : 'none';

        const photoContainer = document.getElementById('userPhotoContainer');
        if (editMode) {
            photoContainer.classList.add('edit-mode');
        } else {
            photoContainer.classList.remove('edit-mode');
        }

        const skillsList = document.querySelector('.skills-list');
        if (editMode) {
            skillsList.classList.add('edit-mode');
            // Add click handlers to all skills
            skillsList.querySelectorAll('li').forEach(skill => {
                skill.onclick = function() {
                    this.remove();  // Remove without confirmation
                };
            });
        } else {
            skillsList.classList.remove('edit-mode');
            // Remove click handlers
            skillsList.querySelectorAll('li').forEach(skill => {
                skill.onclick = null;
            });
        }
    }

    document.addEventListener('DOMContentLoaded', function () {
        document.querySelector('.photo-overlay').addEventListener('click', function () {
            if (document.getElementById('userPhotoContainer').classList.contains('edit-mode')) {
                document.getElementById('profileImage').click();
            }
        });
    });

    function previewImage(event) {
        const reader = new FileReader();
        reader.onload = function () {
            const output = document.getElementById('userProfilePic');
            output.src = reader.result;
        }
        reader.readAsDataURL(event.target.files[0]);
    }

    function updateName() {
        const newName = document.getElementById('editName').value;
        document.getElementById('userName').textContent = newName;
    }

    function updateBio() {
        const bio = document.getElementById('userBio');
        const input = document.getElementById('editBio');
        const newBio = input.value.trim();
        bio.textContent = newBio;
    }

    document.addEventListener('DOMContentLoaded', function() {
        const editBio = document.getElementById('editBio');
        if (editBio) {
            editBio.addEventListener('input', updateBio);
        }
    });

    function addSkill() {
        const skill = document.getElementById('addSkillInput').value.trim();
        if (skill) {
            const li = document.createElement('li');
            li.textContent = skill;
            if (document.querySelector('.skills-list').classList.contains('edit-mode')) {
                li.onclick = function() {
                    this.remove();  // Remove without confirmation
                };
            }
            document.querySelector('.skills-list').appendChild(li);
            document.getElementById('addSkillInput').value = '';
        }
    }

    function autoResize(textarea) {
        textarea.style.height = 'auto';
        textarea.style.height = textarea.scrollHeight + 'px';
    }

    function saveProfileChanges() {
        const newName = document.getElementById('editName').value;
        const newBio = document.getElementById('editBio').value;
        const skills = [];
        const skillItems = document.querySelectorAll('.skills-list li');
        skillItems.forEach(skill => {
            skills.push(skill.textContent);
        });

        const formData = new FormData();
        formData.append('name', newName);
        formData.append('bio', newBio);
        formData.append('skills', skills.join(','));

        const profileImage = document.getElementById('profileImage').files[0];
        if (profileImage) {
            formData.append('profile_photo', profileImage, profileImage.name); // Add filename
        }

        // Show loading state
        const saveButton = document.getElementById('saveButton');
        saveButton.textContent = 'Saving...';
        saveButton.disabled = true;

        fetch('../actions/action_update_profile.php', { // Update the path
            method: 'POST',
            body: formData,
            credentials: 'same-origin'
        })
        .then(response => {
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            return response.json();
        })
        .then(data => {
            if (data.success) {
                // Update UI with new data
                if (data.profile_photo) {
                    document.getElementById('userProfilePic').src = '../docs/' + data.profile_photo;
                }
                toggleEditProfile();
                // Reload header photo if it exists
                const headerPhoto = document.querySelector('.user-icon');
                if (headerPhoto && data.profile_photo) {
                    headerPhoto.src = '../docs/' + data.profile_photo;
                }
            } else {
                throw new Error(data.error || 'Unknown error occurred');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Failed to save profile: ' + error.message);
        })
        .finally(() => {
            // Reset button state
            saveButton.textContent = 'Save';
            saveButton.disabled = false;
        });
    }

</script>
</body>
</html>
