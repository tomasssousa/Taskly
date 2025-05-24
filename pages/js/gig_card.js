document.addEventListener('click', function(e) {
    document.querySelectorAll('.gig-menu').forEach(menu => {
        menu.style.display = 'none';
    });
});

document.querySelectorAll('.gig-settings-btn').forEach(button => {
    button.addEventListener('click', function(e) {
        e.preventDefault();
        e.stopPropagation();
        const gigId = this.dataset.gigId;
        const menu = document.getElementById(`gigMenu_${gigId}`);
        // Close other menus
        document.querySelectorAll('.gig-menu').forEach(m => {
            if (m.id !== `gigMenu_${gigId}`) m.style.display = 'none';
        });
        menu.style.display = menu.style.display === 'none' ? 'block' : 'none';
    });
});

// Add task click handler
document.querySelectorAll('.gig-card').forEach(card => {
    card.addEventListener('click', function(e) {
        // Don't navigate if clicking on interactive elements
        if (!e.target.closest('.gig-actions') && 
            !e.target.closest('.carousel-btn') && 
            !e.target.closest('.dot') &&
            !e.target.closest('.user-profile-link')) {
            const gigId = this.dataset.gigId;
            window.location.href = `task.php?id=${gigId}`;
        }
    });
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