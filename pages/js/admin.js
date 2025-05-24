
function promoteUser(username) {
    if (confirm(`Are you sure you want to promote "${username}" to admin? This action gives them full admin privileges.`)) {
        fetch('../actions/action_admin_promote_user.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                username: username
            }),
            credentials: 'same-origin'
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('User promoted to admin successfully');
                location.reload();
            } else {
                alert('Error: ' + (data.error || 'Failed to promote user'));
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Failed to promote user');
        });
    }
}

function deleteUser(username) {
    if (confirm(`Are you sure you want to delete user "${username}"? This action cannot be undone.`)) {
        fetch('../actions/action_admin_delete_user.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                username: username
            }),
            credentials: 'same-origin'
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('User deleted successfully');
                location.reload();
            } else {
                alert('Error: ' + (data.error || 'Failed to delete user'));
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Failed to delete user');
        });
    }
}

function deleteService(serviceId) {
    if (confirm(`Are you sure you want to delete service ID ${serviceId}? This action cannot be undone.`)) {
        fetch('../actions/action_admin_delete_service.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                service_id: serviceId
            }),
            credentials: 'same-origin'
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('Service deleted successfully');
                location.reload();
            } else {
                alert('Error: ' + (data.error || 'Failed to delete service'));
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Failed to delete service');
        });
    }
}