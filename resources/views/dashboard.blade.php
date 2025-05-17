<x-app-layout>
    <link rel="stylesheet" href="{{ asset('css/dashboard.css') }}">
    <link rel="stylesheet" href="/css/dashboard.css">
    
    <div class="dashboard-container">
        <!-- Tarjetas de Resumen -->
        <div class="stats-cards">
            <div class="stat-card">
                <div class="stat-icon bg-primary">
                    <i class="fas fa-school"></i>
                </div>
                <div class="stat-details">
                    <h3>Total Colegios</h3>
                    <p class="stat-number">145</p>
                    <span class="stat-change positive">
                        <i class="fas fa-arrow-up"></i> 12% vs mes anterior
                    </span>
                </div>
            </div>

            <div class="stat-card">
                <div class="stat-icon bg-success">
                    <i class="fas fa-users"></i>
                </div>
                <div class="stat-details">
                    <h3>Total Estudiantes</h3>
                    <p class="stat-number">2,567</p>
                    <span class="stat-change positive">
                        <i class="fas fa-arrow-up"></i> 15% vs mes anterior
                    </span>
                </div>
            </div>

            <div class="stat-card">
                <div class="stat-icon bg-info">
                    <i class="fas fa-chalkboard-teacher"></i>
                </div>
                <div class="stat-details">
                    <h3>Total Tutores</h3>
                    <p class="stat-number">89</p>
                    <span class="stat-change positive">
                        <i class="fas fa-arrow-up"></i> 5% vs mes anterior
                    </span>
                </div>
            </div>

            <div class="stat-card">
                <div class="stat-icon bg-warning">
                    <i class="fas fa-trophy"></i>
                </div>
                <div class="stat-details">
                    <h3>Convocatorias Activas</h3>
                    <p class="stat-number">3</p>
                    <span class="stat-change neutral">
                        <i class="fas fa-minus"></i> Sin cambios
                    </span>
                </div>
            </div>

            <!-- Nuevas tarjetas de estadísticas -->
            <div class="stat-card">
                <div class="stat-icon bg-purple">
                    <i class="fas fa-chart-line"></i>
                </div>
                <div class="stat-details">
                    <h3>Promedio por Colegio</h3>
                    <p class="stat-number">17.7</p>
                    <span class="stat-change positive">
                        <i class="fas fa-arrow-up"></i> 2.3 vs promedio anterior
                    </span>
                </div>
            </div>

            <div class="stat-card">
                <div class="stat-icon bg-cyan">
                    <i class="fas fa-percentage"></i>
                </div>
                <div class="stat-details">
                    <h3>Tasa de Participación</h3>
                    <p class="stat-number">78%</p>
                    <span class="stat-change positive">
                        <i class="fas fa-arrow-up"></i> 5% vs mes anterior
                    </span>
                </div>
            </div>
        </div>

        <!-- Filtros y Controles -->
        <div class="filters-section">
            <div class="filter-group">
                <label>Año:</label>
                <select class="filter-select" id="yearFilter">
                    <option value="2024">2024</option>
                    <option value="2023">2023</option>
                </select>
            </div>
            <div class="filter-group">
                <label>Departamento:</label>
                <select class="filter-select" id="regionFilter">
                    <option value="todos">Todos</option>
                    <option value="la-paz">La Paz</option>
                    <option value="cochabamba">Cochabamba</option>
                    <option value="santa-cruz">Santa Cruz</option>
                </select>
            </div>
            <div class="filter-group">
                <label>Nivel:</label>
                <select class="filter-select" id="levelFilter">
                    <option value="todos">Todos</option>
                    <option value="primaria">Primaria</option>
                    <option value="secundaria">Secundaria</option>
                </select>
            </div>
        </div>

        <!-- Gráficos Principales -->
        <div class="charts-grid">
              <!-- Gráfico de Participación por Departamento -->
            <div class="chart-card full-width">
                <h3>Participación por Departamento</h3>
                <canvas id="departamentosChart"></canvas>
            </div>
            <!-- Gráfico de Participación por Área -->
            <div class="chart-card">
                <h3>Participación por Área</h3>
                <canvas id="areasChart"></canvas>
            </div>

            <!-- Gráfico de Distribución por Tipo de Colegio -->
            <div class="chart-card">
                <h3>Distribución por Tipo de Colegio</h3>
                <canvas id="tipoColegioChart"></canvas>
            </div>

            <!-- Gráfico de Niveles de Participación -->
            <div class="chart-card">
                <h3>Niveles de Participación</h3>
                <canvas id="nivelesChart"></canvas>
            </div>

            <!-- Gráfico de Distribución por Género -->
            <div class="chart-card">
                <h3>Distribución por Género</h3>
                <canvas id="generoChart"></canvas>
            </div>
        </div>

        <!-- Estadísticas Detalladas -->
        <div class="stats-details">
            <div class="stats-section">
                <h3>Top 5 Colegios con Mayor Participación</h3>
                <div class="stats-table-container">
                    <table class="stats-table">
                        <thead>
                            <tr>
                                <th>Colegio</th>
                                <th>Estudiantes</th>
                                <th>% del Total</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>San Calixto</td>
                                <td>250</td>
                                <td>9.7%</td>
                            </tr>
                            <tr>
                                <td>La Salle</td>
                                <td>215</td>
                                <td>8.4%</td>
                            </tr>
                            <tr>
                                <td>Don Bosco</td>
                                <td>198</td>
                                <td>7.7%</td>
                            </tr>
                            <tr>
                                <td>San Ignacio</td>
                                <td>185</td>
                                <td>7.2%</td>
                            </tr>
                            <tr>
                                <td>Amor de Dios</td>
                                <td>172</td>
                                <td>6.7%</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Rankings y Alertas -->
        <div class="rankings-alerts-grid">
            <!-- Top Tutores -->
            <div class="ranking-card">
                <div class="card-header">
                    <h3><i class="fas fa-star"></i> Top 5 Tutores</h3>
                    <span class="period-badge">Este mes</span>
                </div>
                <div class="ranking-list">
                    <div class="ranking-item">
                        <span class="ranking-position gold">1</span>
                        <div class="ranking-info">
                            <h4>Juan Pérez</h4>
                            <p>32 estudiantes</p>
                            <div class="tags">
                                <span class="tag">Matemáticas</span>
                                <span class="tag">Física</span>
                            </div>
                        </div>
                        <span class="ranking-score">95%</span>
                    </div>
                    
                    <div class="ranking-item">
                        <span class="ranking-position silver">2</span>
                        <div class="ranking-info">
                            <h4>María González</h4>
                            <p>28 estudiantes</p>
                            <div class="tags">
                                <span class="tag">Química</span>
                            </div>
                        </div>
                        <span class="ranking-score">92%</span>
                    </div>
                    
                    <div class="ranking-item">
                        <span class="ranking-position bronze">3</span>
                        <div class="ranking-info">
                            <h4>Carlos Rodríguez</h4>
                            <p>25 estudiantes</p>
                            <div class="tags">
                                <span class="tag">Biología</span>
                            </div>
                        </div>
                        <span class="ranking-score">88%</span>
                    </div>
                </div>
            </div>

            <!-- Alertas -->
            <div class="alerts-card">
                <div class="card-header">
                    <h3><i class="fas fa-bell"></i> Alertas del Sistema</h3>
                    <button class="refresh-btn"><i class="fas fa-sync-alt"></i></button>
                </div>
                <div class="alerts-list">
                    <div class="alert-item warning">
                        <i class="fas fa-exclamation-triangle"></i>
                        <div class="alert-content">
                            <h4>Baja Participación</h4>
                            <p>3 colegios sin actividad este mes</p>
                            <span class="alert-time">Hace 2 horas</span>
                        </div>
                        <button class="alert-action">Ver</button>
                    </div>

                    <div class="alert-item danger">
                        <i class="fas fa-times-circle"></i>
                        <div class="alert-content">
                            <h4>Inscripciones Pendientes</h4>
                            <p>5 estudiantes con datos incompletos</p>
                            <span class="alert-time">Hace 4 horas</span>
                        </div>
                        <button class="alert-action">Revisar</button>
                    </div>

                    <div class="alert-item success">
                        <i class="fas fa-check-circle"></i>
                        <div class="alert-content">
                            <h4>Nueva Olimpiada Creada</h4>
                            <p>Matemáticas 2024 - Segunda Fase</p>
                            <span class="alert-time">Hace 1 día</span>
                        </div>
                        <button class="alert-action">Detalles</button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/leaflet@1.7.1/dist/leaflet.js"></script>
    <script src="{{ asset('js/dashboard.js') }}"></script>
    <script src="/js/dashboard.js"></script>
</x-app-layout>
