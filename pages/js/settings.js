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
        console.log('Button clicked:', this.dataset.type);
        
        document.querySelectorAll('.account-type-btn').forEach(b => b.classList.remove('active'));
        this.classList.add('active');
        
        const hiddenInput = document.getElementById('account_type');
        hiddenInput.value = this.dataset.type;
        
        console.log('Hidden input value set to:', hiddenInput.value);
    });
});

// Debug: Log form submission
document.querySelector('form[action="../actions/action_update_account.php"]')?.addEventListener('submit', function(e) {
    const accountType = document.getElementById('account_type').value;
    console.log('Form submitting with account_type:', accountType);
    
    // Show all form data
    const formData = new FormData(this);
    for (let [key, value] of formData.entries()) {
        console.log(key, ':', value);
    }
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
