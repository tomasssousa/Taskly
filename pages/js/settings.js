document.querySelectorAll('.tab-btn').forEach(btn => {
    btn.addEventListener('click', function() {
        document.querySelectorAll('.tab-btn').forEach(b => b.classList.remove('active'));
        this.classList.add('active');
        document.querySelectorAll('.tab-content').forEach(tab => tab.style.display = 'none');
        document.getElementById(this.dataset.tab + '-tab').style.display = '';
    });
});

document.querySelectorAll('.account-type-btn').forEach(btn => {
    btn.addEventListener('click', function() {
        document.querySelectorAll('.account-type-btn').forEach(b => b.classList.remove('active'));
        this.classList.add('active');
    });
});

const newPasswordField = document.getElementById('new_password');
const confirmPasswordField = document.getElementById('confirm_password');

if (newPasswordField && confirmPasswordField) {
    confirmPasswordField.addEventListener('input', function() {
        if (newPasswordField.value !== confirmPasswordField.value) {
            confirmPasswordField.setCustomValidity('Passwords do not match');
        } else {
            confirmPasswordField.setCustomValidity('');
        }
    });
    
    newPasswordField.addEventListener('input', function() {
        if (newPasswordField.value !== confirmPasswordField.value) {
            confirmPasswordField.setCustomValidity('Passwords do not match');
        } else {
            confirmPasswordField.setCustomValidity('');
        }
    });
}
