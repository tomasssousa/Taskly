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

let selectedRating = 0;

function setRating(rating) {
    selectedRating = rating;
    document.getElementById('ratingValue').value = rating;
    
    // Update star display
    const stars = document.querySelectorAll('.star-input');
    stars.forEach((star, index) => {
        if (index < rating) {
            star.classList.add('selected');
        } else {
            star.classList.remove('selected');
        }
    });
}

function submitReview(event) {
    event.preventDefault();
    
    const rating = document.getElementById('ratingValue').value;
    const reviewText = document.getElementById('reviewText').value.trim();
    const taskId = new URLSearchParams(window.location.search).get('id');
    
    if (rating == 0) {
        alert('Please select a rating');
        return;
    }
    
    if (!reviewText) {
        alert('Please write a review');
        return;
    }
    
    // Send review to backend
    fetch('../actions/action_add_review.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({
            taskId: taskId,
            rating: rating,
            reviewText: reviewText
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('Review submitted successfully!');
            location.reload(); // Refresh to show new review
        } else {
            alert('Error: ' + data.error);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('An error occurred while submitting your review.');
    });
}

// Add hover effect for star rating
document.addEventListener('DOMContentLoaded', function() {
    const stars = document.querySelectorAll('.star-input');
    
    stars.forEach((star, index) => {
        star.addEventListener('mouseenter', function() {
            stars.forEach((s, i) => {
                if (i <= index) {
                    s.style.color = '#ffc107';
                } else {
                    s.style.color = '#ddd';
                }
            });
        });
        
        star.addEventListener('mouseleave', function() {
            stars.forEach((s, i) => {
                if (i < selectedRating) {
                    s.style.color = '#ffc107';
                } else {
                    s.style.color = '#ddd';
                }
            });
        });
    });
});