function toggleFavorite(serviceId) {
    const favoriteBtn = document.getElementById('favoriteBtn');
    const star = favoriteBtn.querySelector('.favorite-star');
    const text = favoriteBtn.querySelector('.favorite-text');
    
    // Disable button during request
    favoriteBtn.disabled = true;
    
    fetch('../actions/action_toggle_favorite.php', {
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
            throw new Error('Network response was not ok');
        }
        return response.json();
    })
    .then(data => {
        console.log('Server response:', data); // Debug log
        
        if (data.success) {
            if (data.is_favorite) {
                favoriteBtn.classList.add('favorited');
                text.textContent = 'Remove from favorites';
            } else {
                favoriteBtn.classList.remove('favorited');
                text.textContent = 'Add to favorites';
            }
        } else {
            alert('Error: ' + data.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('An error occurred. Please try again.');
    })
    .finally(() => {
        favoriteBtn.disabled = false;
    });
}