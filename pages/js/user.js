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

document.querySelector('.photo-overlay').addEventListener('click', function () {
    if (document.getElementById('userPhotoContainer').classList.contains('edit-mode')) {
        document.getElementById('profileImage').click();
    }
});

const editBio = document.getElementById('editBio');
if (editBio) {
    editBio.addEventListener('input', updateBio);
}

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
        saveButton.textContent = 'Save';
        saveButton.disabled = false;
    });
}