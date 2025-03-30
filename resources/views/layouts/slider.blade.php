<div class="sidebar">
    <div class="sidebar-header">
        <div class="logo-container">
            <h2 class="logo-text">Pestañas</h2>
        </div>
        <button type="button" id="sidebarCollapseBtn" class="sidebar-collapse-btn">
            <i class="fas fa-chevron-left"></i>
        </button>
    </div>
    
    <div class="sidebar-divider">
        <span>OPCIONES</span>
    </div>
    
    <ul class="sidebar-menu">
        <li class="{{ request()->routeIs('dashboard') ? 'active' : '' }}">
            <a href="{{ route('dashboard') }}">
                <i class="fas fa-home"></i>
                <span>Dashboard</span>
            </a>
        </li>
        <li>
            <a href="#">
                <i class="fas fa-school"></i>
                <span>Gestión de Colegios</span>
                <i class="fas fa-chevron-right submenu-arrow"></i>
            </a>
        </li>
        <li>
            <a href="#">
                <i class="fas fa-bullhorn"></i>
                <span>Gestión de Convocatorias</span>
                <i class="fas fa-chevron-right submenu-arrow"></i>
            </a>
        </li>
        <li>
            <a href="#">
                <i class="fas fa-book"></i>
                <span>Gestión de Áreas</span>
            </a>
        </li>
        <li>
            <a href="#">
                <i class="fas fa-user-plus"></i>
                <span>Registro</span>
            </a>
        </li>
        <li>
            <a href="#">
                <i class="fas fa-cog"></i>
                <span>Configuración</span>
            </a>
        </li>
    </ul>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const sidebarCollapseBtn = document.getElementById('sidebarCollapseBtn');
    const appWrapper = document.querySelector('.app-wrapper') || document.body;
    
    // Check for saved state
    const sidebarState = localStorage.getItem('sidebarCollapsed');
    if (sidebarState === 'true') {
        appWrapper.classList.add('sidebar-collapsed');
        if (sidebarCollapseBtn) {
            sidebarCollapseBtn.querySelector('i').classList.remove('fa-chevron-left');
            sidebarCollapseBtn.querySelector('i').classList.add('fa-chevron-right');
        }
    }
    
    if (sidebarCollapseBtn) {
        sidebarCollapseBtn.addEventListener('click', function() {
            appWrapper.classList.toggle('sidebar-collapsed');
            
            // Toggle icon direction
            const icon = this.querySelector('i');
            if (icon.classList.contains('fa-chevron-left')) {
                icon.classList.remove('fa-chevron-left');
                icon.classList.add('fa-chevron-right');
            } else {
                icon.classList.remove('fa-chevron-right');
                icon.classList.add('fa-chevron-left');
            }
            
            // Save state
            localStorage.setItem('sidebarCollapsed', appWrapper.classList.contains('sidebar-collapsed'));
        });
    }
});
</script>