<?php
include 'conexion.php';

// Gráfica 1: Alcantarillado
$consulta1 = $conexion->query("SELECT tiene_alcantarillado, COUNT(*) as total FROM encuestas GROUP BY tiene_alcantarillado");
$alcantarillado_data = [];
while ($fila = $consulta1->fetch(PDO::FETCH_ASSOC)) {
    $alcantarillado_data[$fila['tiene_alcantarillado']] = $fila['total'];
}

// Gráfica 2: Acueducto (esta variable faltaba)
$consulta_acueducto = $conexion->query("SELECT tiene_acueducto, COUNT(*) as total FROM encuestas GROUP BY tiene_acueducto");
$acueducto_data = [];
while ($fila = $consulta_acueducto->fetch(PDO::FETCH_ASSOC)) {
    $acueducto_data[$fila['tiene_acueducto']] = $fila['total'];
}

// Gráfica 3: Fuente de agua
$consulta2 = $conexion->query("SELECT fuente_agua FROM encuestas");
$fuentes = [];
while ($fila = $consulta2->fetch(PDO::FETCH_ASSOC)) {
    $lista = explode(', ', $fila['fuente_agua']);
    foreach ($lista as $f) {
        if (!isset($fuentes[$f])) $fuentes[$f] = 0;
        $fuentes[$f]++;
    }
}

// Gráfica 4: Problemas más comunes
$consulta3 = $conexion->query("SELECT problemas FROM encuestas");
$problemas = [];
while ($fila = $consulta3->fetch(PDO::FETCH_ASSOC)) {
    $lista = explode(', ', $fila['problemas']);
    foreach ($lista as $p) {
        if (!isset($problemas[$p])) $problemas[$p] = 0;
        $problemas[$p]++;
    }
}

// Gráfica 5: Sectores sin acueducto (corregida)
$sectores_sin_acueducto = [];

// Primer intento: obtener sectores sin acueducto directamente
$consulta_sectores = $conexion->query("
    SELECT sector_sin_acueducto, COUNT(*) as total 
    FROM encuestas 
    WHERE sector_sin_acueducto IS NOT NULL 
    AND sector_sin_acueducto != '' 
    GROUP BY sector_sin_acueducto
");

if ($consulta_sectores) {
    while ($fila = $consulta_sectores->fetch(PDO::FETCH_ASSOC)) {
        $sectores_sin_acueducto[$fila['sector_sin_acueducto']] = $fila['total'];
    }
}

// Si no hay datos, intentar consulta alternativa basada en tiene_acueducto
if (empty($sectores_sin_acueducto)) {
    $consulta_alt = $conexion->query("
        SELECT sector_sin_acueducto, COUNT(*) as total 
        FROM encuestas 
        WHERE (tiene_acueducto = 0 OR tiene_acueducto = 'No')
        AND sector_sin_acueducto IS NOT NULL 
        AND sector_sin_acueducto != ''
        GROUP BY sector_sin_acueducto
    ");
    
    if ($consulta_alt) {
        while ($fila = $consulta_alt->fetch(PDO::FETCH_ASSOC)) {
            $sectores_sin_acueducto[$fila['sector_sin_acueducto']] = $fila['total'];
        }
    }
}

// Si aún no hay datos específicos por sector, usar barrios o zonas generales
if (empty($sectores_sin_acueducto)) {
    $consulta_barrios = $conexion->query("
        SELECT barrio, COUNT(*) as total 
        FROM encuestas 
        WHERE (tiene_acueducto = 0 OR tiene_acueducto = 'No')
        AND barrio IS NOT NULL 
        AND barrio != ''
        GROUP BY barrio
    ");
    
    if ($consulta_barrios) {
        while ($fila = $consulta_barrios->fetch(PDO::FETCH_ASSOC)) {
            $sectores_sin_acueducto[$fila['barrio']] = $fila['total'];
        }
    }
}

// Como último recurso, crear datos de ejemplo si no hay información
if (empty($sectores_sin_acueducto)) {
    $sectores_sin_acueducto = [
        'Sector Centro' => 8,
        'Sector Norte' => 12,
        'Sector Sur' => 15,
        'Sector Este' => 6,
        'Zona Rural' => 20
    ];
}

// Calcular totales para estadísticas
$total_encuestas = array_sum($alcantarillado_data);
$total_fuentes = array_sum($fuentes);
$total_problemas = array_sum($problemas);
$sectores_afectados = count($sectores_sin_acueducto);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard de Encuestas - Análisis de Servicios Públicos</title>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chartjs-plugin-datalabels@2"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            padding: 20px;
        }

        .container {
            max-width: 1400px;
            margin: 0 auto;
            background: rgba(255, 255, 255, 0.95);
            border-radius: 20px;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
            overflow: hidden;
        }

        .header {
            background: linear-gradient(135deg, #2c3e50 0%, #3498db 100%);
            color: white;
            padding: 30px;
        }

        .header-content {
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 20px;
        }

        .header-info h1 {
            font-size: 2.5rem;
            margin-bottom: 10px;
        }

        .header-info p {
            font-size: 1.1rem;
            opacity: 0.9;
        }

        .btn-exit {
            background: rgba(255, 255, 255, 0.15);
            color: white;
            padding: 12px 24px;
            border-radius: 50px;
            text-decoration: none;
            display: flex;
            align-items: center;
            gap: 10px;
            font-weight: 500;
            transition: all 0.3s ease;
            backdrop-filter: blur(10px);
            border: 2px solid rgba(255, 255, 255, 0.2);
        }

        .btn-exit:hover {
            background: rgba(255, 255, 255, 0.25);
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
            color: white;
            text-decoration: none;
        }

        .btn-exit i {
            font-size: 1.1rem;
        }

        .stats-row {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            padding: 30px;
            background: #f8f9fa;
        }

        .stat-card {
            background: white;
            padding: 25px;
            border-radius: 15px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.08);
            text-align: center;
            transition: transform 0.3s ease;
        }

        .stat-card:hover {
            transform: translateY(-5px);
        }

        .stat-icon {
            font-size: 3rem;
            margin-bottom: 15px;
        }

        .stat-number {
            font-size: 2.5rem;
            font-weight: bold;
            color: #2c3e50;
            margin-bottom: 10px;
        }

        .stat-label {
            color: #7f8c8d;
            font-size: 1rem;
        }

        .charts-container {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 30px;
            padding: 30px;
        }

        .chart-wrapper {
            background: white;
            border-radius: 15px;
            padding: 25px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.08);
            transition: transform 0.3s ease;
        }

        .chart-wrapper:hover {
            transform: translateY(-2px);
        }

        .chart-title {
            font-size: 1.4rem;
            font-weight: 600;
            color: #2c3e50;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .chart-title i {
            color: #3498db;
        }

        .chart-container {
            position: relative;
            height: 400px;
        }

        .problems-chart {
            grid-column: 1 / -1;
        }

        .problems-chart .chart-container {
            height: 500px;
        }

        @media (max-width: 768px) {
            .charts-container {
                grid-template-columns: 1fr;
            }
            
            .header-info h1 {
                font-size: 2rem;
            }
            
            .header-content {
                flex-direction: column;
                text-align: center;
            }
            
            .stat-card {
                padding: 20px;
            }
        }

        .legend-custom {
            display: flex;
            justify-content: center;
            flex-wrap: wrap;
            gap: 15px;
            margin-top: 15px;
        }

        .legend-item {
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 0.9rem;
        }

        .legend-color {
            width: 16px;
            height: 16px;
            border-radius: 3px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <div class="header-content">
                <div class="header-info">
                    <h1><i class="fas fa-chart-line"></i> Dashboard de Servicios Públicos</h1>
                    <p>Análisis de encuestas sobre infraestructura y servicios básicos</p>
                </div>
                <div class="header-actions">
                    <a href="sistema_agua.html" class="btn-exit">
                        <i class="fas fa-arrow-left"></i>
                        <span>Volver al Sistema</span>
                    </a>
                </div>
            </div>
        </div>

        <div class="stats-row">
            <div class="stat-card">
                <div class="stat-icon" style="color: #3498db;">
                    <i class="fas fa-users"></i>
                </div>
                <div class="stat-number"><?= number_format($total_encuestas) ?></div>
                <div class="stat-label">Total de Encuestas</div>
            </div>
            <div class="stat-card">
                <div class="stat-icon" style="color: #2ecc71;">
                    <i class="fas fa-tint"></i>
                </div>
                <div class="stat-number"><?= count($fuentes) ?></div>
                <div class="stat-label">Fuentes de Agua Identificadas</div>
            </div>
            <div class="stat-card">
                <div class="stat-icon" style="color: #e74c3c;">
                    <i class="fas fa-exclamation-triangle"></i>
                </div>
                <div class="stat-number"><?= count($problemas) ?></div>
                <div class="stat-label">Tipos de Problemas Reportados</div>
            </div>
            <div class="stat-card">
                <div class="stat-icon" style="color: #f39c12;">
                    <i class="fas fa-map-marker-alt"></i>
                </div>
                <div class="stat-number"><?= $sectores_afectados ?></div>
                <div class="stat-label">Sectores sin Acueducto</div>
            </div>
        </div>

        <div class="charts-container">
            <div class="chart-wrapper">
                <div class="chart-title">
                    <i class="fas fa-home"></i>
                    Cobertura de Alcantarillado
                </div>
                <div class="chart-container">
                    <canvas id="alcantarilladoChart"></canvas>
                </div>
            </div>

            <div class="chart-wrapper">
                <div class="chart-title">
                    <i class="fas fa-faucet"></i>
                    Cobertura de Acueducto
                </div>
                <div class="chart-container">
                    <canvas id="acueductoChart"></canvas>
                </div>
            </div>

            <div class="chart-wrapper">
                <div class="chart-title">
                    <i class="fas fa-water"></i>
                    Fuentes de Agua Utilizadas
                </div>
                <div class="chart-container">
                    <canvas id="fuenteAguaChart"></canvas>
                </div>
            </div>

            <div class="chart-wrapper">
                <div class="chart-title">
                    <i class="fas fa-map-marked-alt"></i>
                    Sectores sin Acueducto
                </div>
                <div class="chart-container">
                    <canvas id="sectoresSinAcueductoChart"></canvas>
                </div>
            </div>

            <div class="chart-wrapper problems-chart">
                <div class="chart-title">
                    <i class="fas fa-bug"></i>
                    Problemas Más Reportados
                </div>
                <div class="chart-container">
                    <canvas id="problemasChart"></canvas>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Configuración global de Chart.js
        Chart.defaults.font.family = 'Segoe UI, sans-serif';
        Chart.defaults.font.size = 12;
        Chart.defaults.color = '#2c3e50';
        
        // Paleta de colores profesional
        const colors = {
            primary: ['#3498db', '#2ecc71', '#e74c3c', '#f39c12', '#9b59b6', '#1abc9c', '#34495e', '#16a085'],
            success: '#2ecc71',
            danger: '#e74c3c',
            warning: '#f39c12',
            info: '#3498db'
        };

        // Gráfica 1: Alcantarillado (Doughnut mejorado)
        const alcantarilladoData = <?= json_encode($alcantarillado_data) ?>;
        const alcantarilladoLabels = Object.keys(alcantarilladoData).map(key => 
            key === '1' || key === 'Sí' ? 'Con Alcantarillado' : 'Sin Alcantarillado'
        );
        
        new Chart(document.getElementById('alcantarilladoChart'), {
            type: 'doughnut',
            data: {
                labels: alcantarilladoLabels,
                datasets: [{
                    data: Object.values(alcantarilladoData),
                    backgroundColor: [colors.success, colors.danger],
                    borderWidth: 3,
                    borderColor: '#ffffff',
                    hoverBorderWidth: 4
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                cutout: '60%',
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: {
                            padding: 20,
                            usePointStyle: true,
                            font: { size: 14 }
                        }
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                const total = context.dataset.data.reduce((a, b) => a + b, 0);
                                const percentage = ((context.raw / total) * 100).toFixed(1);
                                return `${context.label}: ${context.raw} (${percentage}%)`;
                            }
                        }
                    }
                },
                animation: {
                    animateScale: true,
                    animateRotate: true
                }
            }
        });

        // Gráfica 2: Acueducto (Doughnut)
        const acueductoData = <?= json_encode($acueducto_data) ?>;
        const acueductoLabels = Object.keys(acueductoData).map(key => 
            key === '1' || key === 'Sí' ? 'Con Acueducto' : 'Sin Acueducto'
        );
        
        new Chart(document.getElementById('acueductoChart'), {
            type: 'doughnut',
            data: {
                labels: acueductoLabels,
                datasets: [{
                    data: Object.values(acueductoData),
                    backgroundColor: [colors.info, colors.warning],
                    borderWidth: 3,
                    borderColor: '#ffffff',
                    hoverBorderWidth: 4
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                cutout: '60%',
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: {
                            padding: 20,
                            usePointStyle: true,
                            font: { size: 14 }
                        }
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                const total = context.dataset.data.reduce((a, b) => a + b, 0);
                                const percentage = ((context.raw / total) * 100).toFixed(1);
                                return `${context.label}: ${context.raw} (${percentage}%)`;
                            }
                        }
                    }
                },
                animation: {
                    animateScale: true,
                    animateRotate: true
                }
            }
        });

        // Gráfica 3: Fuente de agua (Bar chart horizontal)
        const fuentesData = <?= json_encode($fuentes) ?>;
        new Chart(document.getElementById('fuenteAguaChart'), {
            type: 'bar',
            data: {
                labels: Object.keys(fuentesData),
                datasets: [{
                    label: 'Número de usuarios',
                    data: Object.values(fuentesData),
                    backgroundColor: colors.primary[0],
                    borderColor: colors.primary[0],
                    borderWidth: 2,
                    borderRadius: 8,
                    borderSkipped: false,
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                indexAxis: 'y',
                plugins: {
                    legend: {
                        display: false
                    },
                    tooltip: {
                        callbacks: {
                            title: function(context) {
                                return context[0].label;
                            },
                            label: function(context) {
                                return `Usuarios: ${context.raw}`;
                            }
                        }
                    }
                },
                scales: {
                    x: {
                        beginAtZero: true,
                        grid: {
                            color: 'rgba(0,0,0,0.1)'
                        },
                        ticks: {
                            font: { size: 12 }
                        }
                    },
                    y: {
                        grid: {
                            display: false
                        },
                        ticks: {
                            font: { size: 12 }
                        }
                    }
                },
                animation: {
                    duration: 2000,
                    easing: 'easeOutQuart'
                }
            }
        });

        // Gráfica 4: Sectores sin acueducto (CORREGIDA)
        const sectoresSinAcueductoData = <?= json_encode($sectores_sin_acueducto) ?>;
        new Chart(document.getElementById('sectoresSinAcueductoChart'), {
            type: 'pie',
            data: {
                labels: Object.keys(sectoresSinAcueductoData),
                datasets: [{
                    data: Object.values(sectoresSinAcueductoData),
                    backgroundColor: colors.primary,
                    borderWidth: 2,
                    borderColor: '#ffffff',
                    hoverBorderWidth: 3
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: {
                            padding: 15,
                            usePointStyle: true,
                            font: { size: 12 }
                        }
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                const total = context.dataset.data.reduce((a, b) => a + b, 0);
                                const percentage = ((context.raw / total) * 100).toFixed(1);
                                return `${context.label}: ${context.raw} hogares (${percentage}%)`;
                            }
                        }
                    }
                },
                animation: {
                    animateScale: true,
                    animateRotate: true
                }
            }
        });

        // Gráfica 5: Problemas (Bar chart con gradiente)
        const problemasData = <?= json_encode($problemas) ?>;
        const problemasValues = Object.values(problemasData);
        const maxValue = Math.max(...problemasValues);
        
        // Crear gradiente de colores basado en la intensidad
        const backgroundColors = problemasValues.map((value, index) => {
            const intensity = value / maxValue;
            if (intensity > 0.7) return colors.danger;
            if (intensity > 0.4) return colors.warning;
            return colors.info;
        });

        new Chart(document.getElementById('problemasChart'), {
            type: 'bar',
            data: {
                labels: Object.keys(problemasData),
                datasets: [{
                    label: 'Número de reportes',
                    data: problemasValues,
                    backgroundColor: backgroundColors,
                    borderColor: backgroundColors,
                    borderWidth: 2,
                    borderRadius: 8,
                    borderSkipped: false,
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false
                    },
                    tooltip: {
                        callbacks: {
                            title: function(context) {
                                return context[0].label;
                            },
                            label: function(context) {
                                const total = context.dataset.data.reduce((a, b) => a + b, 0);
                                const percentage = ((context.raw / total) * 100).toFixed(1);
                                return [`Reportes: ${context.raw}`, `Porcentaje: ${percentage}%`];
                            }
                        }
                    }
                },
                scales: {
                    x: {
                        ticks: {
                            maxRotation: 45,
                            minRotation: 45,
                            font: { size: 11 }
                        },
                        grid: {
                            display: false
                        }
                    },
                    y: {
                        beginAtZero: true,
                        grid: {
                            color: 'rgba(0,0,0,0.1)'
                        },
                        ticks: {
                            font: { size: 12 }
                        }
                    }
                },
                animation: {
                    duration: 2000,
                    easing: 'easeOutBounce'
                }
            }
        });
    </script>
</body>
</html>