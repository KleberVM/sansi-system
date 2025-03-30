
<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Dashboard') }}
        </h2>
    </x-slot>

    <!-- Dashboard content with custom classes -->
    <div class="dashboard-container">
        <!-- Stats cards row -->
        <div class="stats-container">
            <div class="stat-card">
                <div class="stat-info">
                    <h3>21,324</h3>
                    <p>Total inscritos postulantes</p>
                    <span class="date-info">12-2023</span>
                </div>
                <div class="stat-icon blue">
                    <i class="fas fa-user-graduate"></i>
                </div>
            </div>
            
            <div class="stat-card">
                <div class="stat-info">
                    <h3>221,324.50</h3>
                    <p>Total inscritos tutores</p>
                    <span class="date-info">12-2023</span>
                </div>
                <div class="stat-icon green">
                    <i class="fas fa-chalkboard-teacher"></i>
                </div>
            </div>
            
            <div class="stat-card">
                <div class="stat-info">
                    <h3>16,703</h3>
                    <p>Total pagos pendientes</p>
                    <span class="date-info">12-2023</span>
                </div>
                <div class="stat-icon yellow">
                    <i class="fas fa-money-bill-wave"></i>
                </div>
            </div>
            
            <div class="stat-card">
                <div class="stat-info">
                    <h3>12.8%</h3>
                    <p>Total pagados</p>
                    <span class="date-info">12-2023</span>
                </div>
                <div class="stat-icon red">
                    <i class="fas fa-chart-line"></i>
                </div>
            </div>
        </div>
        
        <!-- Charts section -->
        <div class="charts-container">
            <div class="chart-card">
                <div class="chart-header">
                    <h3 class="chart-title">Estadísticas de Pagos</h3>
                    <div class="chart-actions">
                        <i class="fas fa-cog"></i>
                    </div>
                </div>
                <div class="chart-content">
                    <img src="{{ asset('img/chart.png') }}" alt="Chart" class="chart-image">
                </div>
                <div class="chart-legend">
                    <div class="legend-item">
                        <span class="legend-color blue"></span>
                        <span>Estudiantes</span>
                    </div>
                    <div class="legend-item">
                        <span class="legend-color green"></span>
                        <span>Tutores</span>
                    </div>
                </div>
            </div>
            
            <div class="chart-card">
                <div class="chart-header">
                    <h3 class="chart-title">Categoría Popular</h3>
                    <div class="chart-actions">
                        <i class="fas fa-cog"></i>
                    </div>
                </div>
                <div class="chart-content">
                    <img src="{{ asset('img/donut-chart.png') }}" alt="Donut Chart" class="chart-image">
                </div>
                <div class="chart-legend">
                    <div class="legend-item">
                        <span class="legend-color blue"></span>
                        <span>Electrónica</span>
                    </div>
                    <div class="legend-item">
                        <span class="legend-color green"></span>
                        <span>Matemática</span>
                    </div>
                    <div class="legend-item">
                        <span class="legend-color purple"></span>
                        <span>Física</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
