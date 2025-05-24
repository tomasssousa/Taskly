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

document.querySelectorAll('.gig-card').forEach(card => {
    card.addEventListener('click', function(e) {
        if (!e.target.closest('.gig-actions') && 
            !e.target.closest('.user-profile-link')) {
            const gigId = this.dataset.gigId;
            window.location.href = `task.php?id=${gigId}`;
        }
    });
});



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