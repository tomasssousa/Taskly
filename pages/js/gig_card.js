function toggleGigMenu(gigId) {
    const menu = document.getElementById(`gigMenu_${gigId}`);
    
    // Close all other menus first
    document.querySelectorAll('.gig-menu').forEach(m => {
        if (m.id !== `gigMenu_${gigId}`) {
            m.style.display = 'none';
        }
    });
    
    menu.style.display = menu.style.display === 'block' ? 'none' : 'block';
}

function showDeleteConfirmation(taskId) {
    if (confirm('Are you sure you want to delete this task? This action cannot be undone.')) {
        deleteTask(taskId);
    }
}

// Close menus when clicking outside
document.addEventListener('click', function(event) {
    const openMenus = document.querySelectorAll('.gig-menu');
    
    openMenus.forEach(menu => {
        if (menu.style.display === 'block') {
            const gigId = menu.id.replace('gigMenu_', '');
            const settingsBtn = document.querySelector(`[data-gig-id="${gigId}"].gig-settings-btn`);
            
            if (!menu.contains(event.target) && 
                !settingsBtn.contains(event.target)) {
                menu.style.display = 'none';
            }
        }
    });
});

// Use event delegation for gig card clicks
document.addEventListener('click', function(e) {
    const gigCard = e.target.closest('.gig-card');
    
    if (gigCard) {
        // Only navigate if NOT clicking on interactive elements
        if (!e.target.closest('.gig-actions') && 
            !e.target.closest('.gig-menu') &&
            !e.target.closest('.gig-seller a')) {
            
            const gigId = gigCard.getAttribute('data-gig-id');
            if (gigId) {
                window.location.href = `task.php?id=${gigId}`;
            }
        }
    }
});

function deleteTask(taskId) {
    fetch(`../actions/action_delete_task.php?id=${taskId}`, {
        method: 'POST',
        credentials: 'same-origin'
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            const gigCard = document.querySelector(`[data-gig-id="${taskId}"]`);
            if (gigCard) {
                gigCard.remove();
            }
            alert('Task deleted successfully!');
        } else {
            throw new Error(data.error || 'Failed to delete task');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Failed to delete task: ' + error.message);
    });
}