<?php
session_start();

// Verificar sesión
if (isset($_GET['action']) && $_GET['action'] == 'logout') {
    session_destroy();
    header("Location: admin_login.php");
    exit;
}

if (!isset($_SESSION['admin_id'])) {
    header("Location: admin_login.php");
    exit;
}

// Configuración BD
$host = 'localhost';
$db_name = 'agua_db';
$username = 'root';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host;port=3309;dbname=$db_name;charset=utf8mb4", $username, $password, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
    ]);
    
    // Procesar acciones CRUD
    $action = $_GET['action'] ?? '';
    $id = $_GET['id'] ?? 0;
    
    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        if ($action == 'edit_user') {
            $nombre = $_POST['nombre'];
            $email = $_POST['email'];
            $password = $_POST['password'];
            
            if (!empty($password)) {
                $stmt = $pdo->prepare("UPDATE usuarios SET nombre = ?, email = ?, password = ? WHERE id = ?");
                $stmt->execute([$nombre, $email, password_hash($password, PASSWORD_DEFAULT), $id]);
            } else {
                $stmt = $pdo->prepare("UPDATE usuarios SET nombre = ?, email = ? WHERE id = ?");
                $stmt->execute([$nombre, $email, $id]);
            }
            $success_msg = "Usuario actualizado correctamente.";
        }
    } elseif ($action == 'delete_user') {
        $pdo->prepare("DELETE FROM usuarios WHERE id = ?")->execute([$id]);
        $success_msg = "Usuario eliminado correctamente.";
    }
    
    // Obtener datos según sección
    $current_section = $_GET['section'] ?? 'dashboard';
    
    // Datos para gráficas (desde encuestas)
    if ($current_section == 'dashboard') {
        // Alcantarillado
        $stmt = $pdo->query("SELECT tiene_alcantarillado, COUNT(*) as total FROM encuestas GROUP BY tiene_alcantarillado");
        $alcantarillado_data = [];
        while ($row = $stmt->fetch()) {
            $alcantarillado_data[$row['tiene_alcantarillado']] = $row['total'];
        }
        
        // Acueducto
        $stmt = $pdo->query("SELECT tiene_acueducto, COUNT(*) as total FROM encuestas GROUP BY tiene_acueducto");
        $acueducto_data = [];
        while ($row = $stmt->fetch()) {
            $acueducto_data[$row['tiene_acueducto']] = $row['total'];
        }
        
        // Fuentes de agua
        $stmt = $pdo->query("SELECT fuente_agua FROM encuestas WHERE fuente_agua IS NOT NULL");
        $fuentes = [];
        while ($row = $stmt->fetch()) {
            $lista = explode(', ', $row['fuente_agua']);
            foreach ($lista as $f) {
                $fuentes[$f] = ($fuentes[$f] ?? 0) + 1;
            }
        }
        
        // Problemas
        $stmt = $pdo->query("SELECT problemas FROM encuestas WHERE problemas IS NOT NULL");
        $problemas = [];
        while ($row = $stmt->fetch()) {
            $lista = explode(', ', $row['problemas']);
            foreach ($lista as $p) {
                $problemas[$p] = ($problemas[$p] ?? 0) + 1;
            }
        }
        
        // Estadísticas generales
        $total_encuestas = $pdo->query("SELECT COUNT(*) FROM encuestas")->fetchColumn();
        $total_usuarios = $pdo->query("SELECT COUNT(*) FROM usuarios")->fetchColumn();
    } elseif ($current_section == 'usuarios') {
        $stmt = $pdo->query("SELECT id, nombre, email FROM usuarios");
        $usuarios = $stmt->fetchAll();
    }
    
    // Datos para edición
    $edit_data = null;
    if ($action == 'edit_user' && $id > 0) {
        $stmt = $pdo->prepare("SELECT id, nombre, email FROM usuarios WHERE id = ?");
        $stmt->execute([$id]);
        $edit_data = $stmt->fetch();
    }
    
} catch (PDOException $e) {
    $error_msg = "Error de conexión: " . $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel Admin - Resultados de Encuestas</title>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        
        body {
            font-family: 'Segoe UI', sans-serif;
            background: #f5f7fa;
            color: #2c3e50;
        }
        
        .header {
            background: linear-gradient(135deg, #2c3e50, #3498db);
            color: white;
            padding: 1rem 2rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .logo { font-size: 1.5rem; font-weight: bold; }
        .nav-menu { display: flex; gap: 2rem; list-style: none; }
        .nav-menu a { color: white; text-decoration: none; padding: 0.5rem 1rem; border-radius: 5px; transition: 0.3s; }
        .nav-menu a:hover, .nav-menu a.active { background: rgba(255,255,255,0.2); }
        .btn-logout { background: #e74c3c; padding: 0.5rem 1rem; border-radius: 5px; color: white; text-decoration: none; }
        
        .container {
            max-width: 1400px;
            margin: 2rem auto;
            padding: 0 1rem;
        }
        
        .content-header {
            background: white;
            padding: 1.5rem;
            border-radius: 10px;
            margin-bottom: 2rem;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1rem;
            margin-bottom: 2rem;
        }
        
        .stat-card {
            background: white;
            padding: 1.5rem;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            text-align: center;
            transition: transform 0.3s;
        }
        
        .stat-card:hover { transform: translateY(-5px); }
        .stat-icon { font-size: 2rem; margin-bottom: 1rem; }
        .stat-number { font-size: 2rem; font-weight: bold; color: #2c3e50; }
        .stat-label { color: #7f8c8d; }
        
        .charts-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(400px, 1fr));
            gap: 2rem;
            margin-bottom: 2rem;
        }
        
        .chart-card {
            background: white;
            padding: 1.5rem;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        
        .chart-title {
            font-size: 1.2rem;
            margin-bottom: 1rem;
            color: #2c3e50;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        
        .chart-container { height: 300px; position: relative; }
        
        .table-container {
            background: white;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        
        table { width: 100%; border-collapse: collapse; }
        th, td { padding: 1rem; text-align: left; border-bottom: 1px solid #eee; }
        th { background: #3498db; color: white; }
        tr:hover { background: #f8f9fa; }
        
        .btn {
            padding: 0.5rem 1rem;
            border-radius: 5px;
            text-decoration: none;
            display: inline-block;
            margin: 0.2rem;
            transition: 0.3s;
        }
        
        .btn-primary { background: #3498db; color: white; }
        .btn-success { background: #2ecc71; color: white; }
        .btn-danger { background: #e74c3c; color: white; }
        .btn:hover { opacity: 0.8; transform: translateY(-2px); }
        
        .form-container {
            background: white;
            padding: 2rem;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            max-width: 500px;
            margin: 0 auto;
        }
        
        .form-group { margin-bottom: 1rem; }
        .form-group label { display: block; margin-bottom: 0.5rem; font-weight: 500; }
        .form-control {
            width: 100%;
            padding: 0.75rem;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 1rem;
        }
        
        .alert {
            padding: 1rem;
            border-radius: 5px;
            margin-bottom: 1rem;
        }
        .alert-success { background: #d4edda; color: #155724; border-left: 4px solid #28a745; }
        .alert-error { background: #f8d7da; color: #721c24; border-left: 4px solid #dc3545; }
        
        @media (max-width: 768px) {
            .header { flex-direction: column; gap: 1rem; }
            .nav-menu { flex-direction: column; }
            .charts-grid { grid-template-columns: 1fr; }
            .content-header { flex-direction: column; gap: 1rem; }
        }
    </style>
</head>
<body>
    <header class="header">
        <div class="logo">
            <i class="fas fa-tint"></i> AquaSolution Admin
        </div>
        <nav>
            <ul class="nav-menu">
                <li><a href="?section=dashboard" class="<?= $current_section == 'dashboard' ? 'active' : '' ?>">
                    <i class="fas fa-chart-pie"></i> Dashboard
                </a></li>
                <li><a href="?section=usuarios" class="<?= $current_section == 'usuarios' ? 'active' : '' ?>">
                    <i class="fas fa-users"></i> Usuarios
                </a></li>
            </ul>
        </nav>
        <div>
            <span>Admin: <?= htmlspecialchars($_SESSION['admin_username'] ?? 'Admin') ?></span>
            <a href="?action=logout" class="btn-logout">
                <i class="fas fa-sign-out-alt"></i> Salir
            </a>
        </div>
    </header>

    <div class="container">
        <?php if (!empty($error_msg)): ?>
            <div class="alert alert-error"><?= htmlspecialchars($error_msg) ?></div>
        <?php endif; ?>
        
        <?php if (!empty($success_msg)): ?>
            <div class="alert alert-success"><?= htmlspecialchars($success_msg) ?></div>
        <?php endif; ?>

        <?php if ($current_section == 'dashboard'): ?>
            <div class="content-header">
                <h2><i class="fas fa-chart-line"></i> Resultados de Encuestas de Agua</h2>
                <div>
                    <a href="sistema_agua.html" class="btn btn-primary">
                        <i class="fas fa-external-link-alt"></i> Ver Sistema
                    </a>
                </div>
            </div>

            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-icon" style="color: #3498db;"><i class="fas fa-poll"></i></div>
                    <div class="stat-number"><?= $total_encuestas ?></div>
                    <div class="stat-label">Total Encuestas</div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon" style="color: #2ecc71;"><i class="fas fa-users"></i></div>
                    <div class="stat-number"><?= $total_usuarios ?></div>
                    <div class="stat-label">Total Usuarios</div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon" style="color: #e74c3c;"><i class="fas fa-tint"></i></div>
                    <div class="stat-number"><?= count($fuentes) ?></div>
                    <div class="stat-label">Fuentes de Agua</div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon" style="color: #f39c12;"><i class="fas fa-exclamation-triangle"></i></div>
                    <div class="stat-number"><?= count($problemas) ?></div>
                    <div class="stat-label">Tipos de Problemas</div>
                </div>
            </div>

            <div class="charts-grid">
                <div class="chart-card">
                    <div class="chart-title">
                        <i class="fas fa-home"></i> Cobertura de Alcantarillado
                    </div>
                    <div class="chart-container">
                        <canvas id="alcantarilladoChart"></canvas>
                    </div>
                </div>

                <div class="chart-card">
                    <div class="chart-title">
                        <i class="fas fa-faucet"></i> Cobertura de Acueducto
                    </div>
                    <div class="chart-container">
                        <canvas id="acueductoChart"></canvas>
                    </div>
                </div>

                <div class="chart-card">
                    <div class="chart-title">
                        <i class="fas fa-water"></i> Fuentes de Agua
                    </div>
                    <div class="chart-container">
                        <canvas id="fuentesChart"></canvas>
                    </div>
                </div>

                <div class="chart-card">
                    <div class="chart-title">
                        <i class="fas fa-bug"></i> Problemas Reportados
                    </div>
                    <div class="chart-container">
                        <canvas id="problemasChart"></canvas>
                    </div>
                </div>
            </div>

        <?php elseif ($current_section == 'usuarios'): ?>
            <?php if ($action == 'edit_user'): ?>
                <div class="form-container">
                    <h3>Editar Usuario</h3>
                    <form method="POST">
                        <div class="form-group">
                            <label>Nombre</label>
                            <input type="text" name="nombre" class="form-control" value="<?= htmlspecialchars($edit_data['nombre'] ?? '') ?>" required>
                        </div>
                        <div class="form-group">
                            <label>Email</label>
                            <input type="email" name="email" class="form-control" value="<?= htmlspecialchars($edit_data['email'] ?? '') ?>" required>
                        </div>
                        <div class="form-group">
                            <label>Nueva Contraseña (opcional)</label>
                            <input type="password" name="password" class="form-control">
                        </div>
                        <div style="display: flex; gap: 1rem; margin-top: 1rem;">
                            <button type="submit" class="btn btn-success">Guardar</button>
                            <a href="?section=usuarios" class="btn btn-danger">Cancelar</a>
                        </div>
                    </form>
                </div>
            <?php else: ?>
                <div class="content-header">
                    <h2><i class="fas fa-users"></i> Gestión de Usuarios</h2>
                </div>

                <div class="table-container">
                    <table>
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Nombre</th>
                                <th>Email</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($usuarios)): ?>
                                <?php foreach ($usuarios as $user): ?>
                                    <tr>
                                        <td><?= $user['id'] ?></td>
                                        <td><?= htmlspecialchars($user['nombre']) ?></td>
                                        <td><?= htmlspecialchars($user['email']) ?></td>
                                        <td>
                                            <a href="?action=edit_user&id=<?= $user['id'] ?>&section=usuarios" class="btn btn-primary">
                                                <i class="fas fa-edit"></i> Editar
                                            </a>
                                            <a href="?action=delete_user&id=<?= $user['id'] ?>&section=usuarios" class="btn btn-danger" 
                                               onclick="return confirm('¿Eliminar usuario?')">
                                                <i class="fas fa-trash"></i> Eliminar
                                            </a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="4" style="text-align: center; padding: 2rem;">
                                        No hay usuarios registrados
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        <?php endif; ?>
    </div>

    <script>
        <?php if ($current_section == 'dashboard'): ?>
        // Configuración de colores
        const colors = ['#3498db', '#2ecc71', '#e74c3c', '#f39c12', '#9b59b6', '#1abc9c'];
        
        // Gráfica Alcantarillado
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
                    backgroundColor: ['#2ecc71', '#e74c3c'],
                    borderWidth: 2
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { position: 'bottom' }
                }
            }
        });

        // Gráfica Acueducto
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
                    backgroundColor: ['#3498db', '#f39c12'],
                    borderWidth: 2
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { position: 'bottom' }
                }
            }
        });

        // Gráfica Fuentes
        const fuentesData = <?= json_encode($fuentes) ?>;
        new Chart(document.getElementById('fuentesChart'), {
            type: 'bar',
            data: {
                labels: Object.keys(fuentesData),
                datasets: [{
                    label: 'Usuarios',
                    data: Object.values(fuentesData),
                    backgroundColor: '#3498db',
                    borderRadius: 5
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false }
                },
                scales: {
                    y: { beginAtZero: true }
                }
            }
        });

        // Gráfica Problemas
        const problemasData = <?= json_encode($problemas) ?>;
        new Chart(document.getElementById('problemasChart'), {
            type: 'bar',
            data: {
                labels: Object.keys(problemasData),
                datasets: [{
                    label: 'Reportes',
                    data: Object.values(problemasData),
                    backgroundColor: colors,
                    borderRadius: 5
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false }
                },
                scales: {
                    y: { beginAtZero: true },
                    x: { 
                        ticks: { 
                            maxRotation: 45,
                            minRotation: 45 
                        }
                    }
                }
            }
        });
        <?php endif; ?>

        // Auto-ocultar alertas
        setTimeout(() => {
            document.querySelectorAll('.alert').forEach(alert => {
                alert.style.opacity = '0';
                setTimeout(() => alert.remove(), 300);
            });
        }, 4000);
    </script>
</body>
</html>