
document.addEventListener('DOMContentLoaded', function() {
    const registerButton = document.getElementById('register-now');
    const modal = document.getElementById('registro-modal');
    const closeButton = modal.querySelector('.close');

    // Open modal when clicking the register button
    registerButton.addEventListener('click', function(e) {
        e.preventDefault();
        modal.style.display = 'block';
    });

    // Close modal when clicking the X button
    closeButton.addEventListener('click', function() {
        modal.style.display = 'none';
    });

    // Close modal when clicking outside
    window.addEventListener('click', function(e) {
        if (e.target == modal) {
            modal.style.display = 'none';
        }
    });
});