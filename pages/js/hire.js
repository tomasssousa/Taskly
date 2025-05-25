    const hireForm = document.getElementById('hireForm');
    const payButton = document.querySelector('.pay-button');
    const buttonText = document.querySelector('.button-text');
    const loadingSpinner = document.querySelector('.loading-spinner');
    const successMessage = document.getElementById('successMessage');

    hireForm.addEventListener('submit', function(e) {
        e.preventDefault();
        
        payButton.disabled = true;
        buttonText.style.display = 'none';
        loadingSpinner.style.display = 'block';
        
        const formData = new FormData();
        formData.append('service_id', document.getElementById('serviceId').value);
        formData.append('phone', document.getElementById('phone').value);
        formData.append('details', document.getElementById('details').value);
        
        fetch('../actions/action_hire_service.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            setTimeout(() => {
                if (data.success) {
                    hireForm.style.display = 'none';
                    document.querySelector('h2').style.display = 'none';
                    document.querySelector('.service-summary').style.display = 'none';
                    successMessage.style.display = 'block';
                } else {
                    alert('Error: ' + data.message);
                    payButton.disabled = false;
                    buttonText.style.display = 'block';
                    loadingSpinner.style.display = 'none';
                }
            }, 2000); 
        })
        .catch(error => {
            setTimeout(() => {
                console.error('Error:', error);
                alert('Payment failed. Please try again.');
                payButton.disabled = false;
                buttonText.style.display = 'block';
                loadingSpinner.style.display = 'none';
            }, 2000);
        });
    });
