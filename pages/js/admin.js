function promoteUser(username) {
    if (!confirm(`Are you sure you want to promote ${username} to admin?`)) {
        return;
    }

    fetch('../actions/action_admin_promote_user.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({
            username: username
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert(data.message);
            location.reload();
        } else {
            alert('Error: ' + data.error);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('An error occurred while promoting the user.');
    });
}

function deleteUser(username) {
    if (!confirm(`Are you sure you want to delete user ${username}? This action cannot be undone.`)) {
        return;
    }

    fetch('../actions/action_delete_account.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({
            username: username
        })
    })
    .then(response => {
        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }
        return response.json();
    })
    .then(data => {
        if (data.success) {
            alert(data.message);
            location.reload();
        } else {
            alert('Error: ' + data.error);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('An error occurred while deleting the user: ' + error.message);
    });
}

function deleteService(serviceId) {
    if (!confirm(`Are you sure you want to delete this service? This action cannot be undone.`)) {
        return;
    }

    fetch('../actions/action_delete_task.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({
            service_id: serviceId
        })
    })
    .then(response => {
        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }
        return response.json();
    })
    .then(data => {
        if (data.success) {
            alert(data.message);
            location.reload();
        } else {
            alert('Error: ' + data.error);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('An error occurred while deleting the service: ' + error.message);
    });
}

function addCategory(event) {
    event.preventDefault();
    
    const categoryName = document.getElementById('categoryName').value.trim();
    
    if (!categoryName) {
        alert('Please enter a category name.');
        return;
    }
    
    // Send to backend API
    fetch('../actions/action_add_category.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({
            categoryName: categoryName
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert(data.message);
            location.reload(); // Refresh to show new category
        } else {
            alert('Error: ' + data.error);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('An error occurred while adding the category.');
    });
}

function deleteCategory(categoryValue) {
    if (confirm(`Are you sure you want to delete the category "${categoryValue}"?`)) {
        fetch('../actions/action_delete_category.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                categoryName: categoryValue
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert(data.message);
                location.reload(); // Refresh to update list
            } else {
                alert('Error: ' + data.error);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('An error occurred while deleting the category.');
        });
    }
}