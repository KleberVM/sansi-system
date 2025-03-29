document.addEventListener('DOMContentLoaded', function() {
    const modal = document.getElementById('register-type-modal');
    const registerBtn = document.querySelector('.register-hero-btn');
    const closeBtn = document.querySelector('.close');

    registerBtn.addEventListener('click', function(e) {
        e.preventDefault();
        modal.classList.add('show');
    });

    closeBtn.addEventListener('click', function() {
        modal.classList.remove('show');
    });

    window.addEventListener('click', function(e) {
        if (e.target == modal) {
            modal.classList.remove('show');
        }
    });
});