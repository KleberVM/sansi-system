document.addEventListener('DOMContentLoaded', function() {
    const sidebarToggle = document.getElementById('sidebarToggle');
    const sidebarClose = document.querySelector('.sidebar-close');
    const appWrapper = document.querySelector('.app-wrapper');
    
    sidebarToggle.addEventListener('click', function() {
        appWrapper.classList.toggle('sidebar-collapsed');
    });
    
    sidebarClose.addEventListener('click', function() {
        appWrapper.classList.remove('sidebar-collapsed');
    });
});