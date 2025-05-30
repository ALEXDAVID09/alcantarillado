<?php
session_start();

// Configuración de la base de datos
$host = 'localhost';
$db_name = 'agua_db';
$username = 'root';
$password = '';
$charset = 'utf8mb4';

$dsn = "mysql:host=$host;port=3309;dbname=$db_name;charset=$charset";

$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

$error_msg = '';

try {
    $pdo = new PDO($dsn, $username, $password, $options);
    
    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        $admin_username = $_POST['username'] ?? '';
        $admin_password = $_POST['password'] ?? '';
        
        if (empty($admin_username) || empty($admin_password)) {
            $error_msg = "Por favor, complete todos los campos.";
        } else {
            // Consulta preparada para el administrador
            $stmt = $pdo->prepare("SELECT id, username, password FROM administradores WHERE username = ?");
            $stmt->execute([$admin_username]);
            $admin = $stmt->fetch();
            
            // Verificar credenciales
            if ($admin && password_verify($admin_password, $admin['password'])) {
                $_SESSION['admin_id'] = $admin['id'];
                $_SESSION['admin_username'] = $admin['username'];
                header("Location: admin_panel.php");
                exit;
            } else {
                $error_msg = "Usuario o contraseña incorrectos.";
            }
        }
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
    <title>Acceso de Administrador - AquaSolution</title>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&family=Space+Grotesk:wght@400;500;600;700&display=swap">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            /* Paleta de colores mejorada */
            --color-primario: #0d4d6b;
            --color-primario-claro: #1a5a7a;
            --color-secundario: #0088a9;
            --color-acento: #00b4d8;
            --color-acento-claro: #33c4e3;
            --color-fondo: #fafbfc;
            --color-fondo-card: #ffffff;
            --color-texto: #1a202c;
            --color-texto-secundario: #4a5568;
            --color-error: #dc2626;
            --color-error-fondo: #fef2f2;
            --color-borde: #e2e8f0;
            --color-borde-focus: #00b4d8;
            
            /* Sombras profesionales */
            --sombra-sm: 0 1px 2px 0 rgba(0, 0, 0, 0.05);
            --sombra-md: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
            --sombra-lg: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
            --sombra-xl: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
            --sombra-focus: 0 0 0 4px rgba(0, 180, 216, 0.15);
            
            /* Transiciones */
            --transicion: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
            --transicion-lenta: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            
            /* Gradientes */
            --gradiente-primario: linear-gradient(135deg, var(--color-primario) 0%, var(--color-secundario) 100%);
            --gradiente-secundario: linear-gradient(135deg, var(--color-acento) 0%, var(--color-acento-claro) 100%);
            --gradiente-fondo: linear-gradient(135deg, #f0f9ff 0%, #e0f2fe 100%);
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
            background: var(--gradiente-fondo);
            color: var(--color-texto);
            line-height: 1.6;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            overflow-x: hidden;
        }

        /* Patrón de fondo sutil */
        body::before {
            content: '';
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-image: 
                radial-gradient(circle at 25% 25%, rgba(0, 180, 216, 0.05) 0%, transparent 50%),
                radial-gradient(circle at 75% 75%, rgba(13, 77, 107, 0.05) 0%, transparent 50%);
            pointer-events: none;
            z-index: 0;
        }

        .container {
            width: 90%;
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 20px;
            position: relative;
            z-index: 1;
        }

        /* Header mejorado */
        header {
            background: rgba(255, 255, 255, 0.9);
            backdrop-filter: blur(20px);
            box-shadow: var(--sombra-sm);
            position: sticky;
            top: 0;
            z-index: 100;
            border-bottom: 1px solid rgba(226, 232, 240, 0.5);
        }

        nav {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 20px 0;
        }

        .logo {
            font-family: 'Space Grotesk', sans-serif;
            font-size: 28px;
            font-weight: 700;
            color: var(--color-primario);
            display: flex;
            align-items: center;
            text-decoration: none;
        }

        .logo i {
            margin-right: 12px;
            color: var(--color-acento);
            font-size: 32px;
            filter: drop-shadow(0 2px 4px rgba(0, 180, 216, 0.3));
        }

        /* Main content */
        main {
            flex: 1;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 60px 0;
        }

        /* Form container premium */
        .form-container {
            background: var(--color-fondo-card);
            border-radius: 24px;
            padding: 50px;
            box-shadow: var(--sombra-xl);
            width: 100%;
            max-width: 480px;
            margin: 0 auto;
            border: 1px solid rgba(226, 232, 240, 0.6);
            transition: var(--transicion-lenta);
            position: relative;
            overflow: hidden;
        }

        .form-container::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: var(--gradiente-primario);
        }

        .form-container:hover {
            transform: translateY(-4px);
            box-shadow: 0 32px 64px -12px rgba(0, 0, 0, 0.12);
        }

        .admin-icon {
            text-align: center;
            margin-bottom: 32px;
            position: relative;
        }

        .admin-icon::before {
            content: '';
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            width: 120px;
            height: 120px;
            background: linear-gradient(135deg, rgba(0, 180, 216, 0.1) 0%, rgba(13, 77, 107, 0.05) 100%);
            border-radius: 50%;
            z-index: 0;
        }

        .admin-icon i {
            font-size: 56px;
            color: var(--color-primario);
            padding: 24px;
            position: relative;
            z-index: 1;
            filter: drop-shadow(0 4px 8px rgba(13, 77, 107, 0.2));
        }

        .form-title {
            text-align: center;
            margin-bottom: 40px;
            color: var(--color-primario);
            font-size: 32px;
            font-weight: 700;
            font-family: 'Space Grotesk', sans-serif;
            position: relative;
            letter-spacing: -0.5px;
        }

        .form-subtitle {
            text-align: center;
            margin-bottom: 32px;
            color: var(--color-texto-secundario);
            font-size: 16px;
            font-weight: 400;
        }

        .form-group {
            margin-bottom: 28px;
            position: relative;
        }

        .form-group label {
            display: block;
            margin-bottom: 10px;
            font-weight: 600;
            color: var(--color-texto);
            font-size: 14px;
            letter-spacing: 0.25px;
        }

        .input-wrapper {
            position: relative;
            display: flex;
            align-items: center;
        }

        .input-icon {
            position: absolute;
            left: 16px;
            color: var(--color-texto-secundario);
            font-size: 18px;
            z-index: 2;
            transition: var(--transicion);
        }

        .form-control {
            width: 100%;
            padding: 16px 16px 16px 52px;
            border: 2px solid var(--color-borde);
            border-radius: 12px;
            font-size: 16px;
            font-weight: 400;
            transition: var(--transicion);
            background-color: var(--color-fondo);
        }

        .form-control:focus {
            border-color: var(--color-borde-focus);
            outline: none;
            box-shadow: var(--sombra-focus);
            background-color: var(--color-fondo-card);
        }

        .form-control:focus + .input-icon,
        .form-control:not(:placeholder-shown) + .input-icon {
            color: var(--color-acento);
            transform: scale(1.1);
        }

        .btn {
            width: 100%;
            background: var(--gradiente-primario);
            color: white;
            padding: 18px 24px;
            border: none;
            border-radius: 12px;
            cursor: pointer;
            font-weight: 600;
            font-size: 16px;
            transition: var(--transicion);
            text-align: center;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 12px;
            letter-spacing: 0.25px;
            position: relative;
            overflow: hidden;
            margin-bottom: 16px;
        }

        .btn::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.2), transparent);
            transition: left 0.5s;
        }

        .btn:hover::before {
            left: 100%;
        }

        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(13, 77, 107, 0.3);
        }

        .btn:active {
            transform: translateY(0);
        }

        /* Botón secundario para registro */
        .btn-secondary {
            background: var(--gradiente-secundario);
            color: white;
            text-decoration: none;
        }

        .btn-secondary:hover {
            box-shadow: 0 8px 25px rgba(0, 180, 216, 0.3);
            text-decoration: none;
            color: white;
        }

        .form-footer {
            text-align: center;
            margin-top: 32px;
            padding-top: 24px;
            border-top: 1px solid var(--color-borde);
        }

        .form-footer a {
            color: var(--color-primario);
            text-decoration: none;
            font-weight: 500;
            transition: var(--transicion);
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 8px 16px;
            border-radius: 8px;
        }

        .form-footer a:hover {
            color: var(--color-acento);
            background-color: rgba(0, 180, 216, 0.05);
        }

        /* Separador visual */
        .divider {
            display: flex;
            align-items: center;
            margin: 20px 0;
            color: var(--color-texto-secundario);
            font-size: 14px;
        }

        .divider::before,
        .divider::after {
            content: '';
            flex: 1;
            height: 1px;
            background: var(--color-borde);
        }

        .divider span {
            padding: 0 16px;
            background: var(--color-fondo-card);
        }

        /* Alertas mejoradas */
        .alert {
            padding: 16px 20px;
            border-radius: 12px;
            margin-bottom: 24px;
            font-size: 14px;
            font-weight: 500;
            display: flex;
            align-items: center;
            gap: 12px;
            animation: slideDown 0.3s ease-out;
        }

        @keyframes slideDown {
            from {
                opacity: 0;
                transform: translateY(-10px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .alert-error {
            background-color: var(--color-error-fondo);
            color: var(--color-error);
            border: 1px solid rgba(220, 38, 38, 0.2);
            border-left: 4px solid var(--color-error);
        }

        .alert i {
            font-size: 18px;
            flex-shrink: 0;
        }

        /* Footer premium */
        footer {
            background: var(--color-primario);
            color: white;
            padding: 60px 0 30px;
            position: relative;
            overflow: hidden;
        }

        footer::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: linear-gradient(135deg, var(--color-primario) 0%, var(--color-primario-claro) 100%);
        }

        .footer-content {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 40px;
            margin-bottom: 40px;
            position: relative;
            z-index: 1;
        }

        .footer-column h3 {
            margin-bottom: 24px;
            font-size: 20px;
            font-weight: 700;
            font-family: 'Space Grotesk', sans-serif;
            position: relative;
            padding-bottom: 12px;
        }

        .footer-column h3::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 0;
            width: 50px;
            height: 3px;
            background: var(--color-acento);
            border-radius: 2px;
        }

        .footer-column ul {
            list-style: none;
        }

        .footer-column ul li {
            margin-bottom: 16px;
        }

        .footer-column a {
            color: rgba(255, 255, 255, 0.8);
            text-decoration: none;
            transition: var(--transicion);
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 4px 0;
            font-weight: 400;
        }

        .footer-column a:hover {
            color: white;
            transform: translateX(4px);
        }

        .footer-column a i {
            font-size: 16px;
            width: 20px;
            flex-shrink: 0;
        }

        .copyright {
            text-align: center;
            margin-top: 40px;
            padding-top: 30px;
            border-top: 1px solid rgba(255, 255, 255, 0.1);
            font-size: 14px;
            color: rgba(255, 255, 255, 0.7);
            position: relative;
            z-index: 1;
        }

        /* Responsive */
        @media (max-width: 768px) {
            .container {
                padding: 0 16px;
            }
            
            .form-container {
                padding: 40px 32px;
                margin: 0 16px;
                border-radius: 20px;
            }

            .form-title {
                font-size: 28px;
            }

            .admin-icon i {
                font-size: 48px;
                padding: 20px;
            }

            .admin-icon::before {
                width: 100px;
                height: 100px;
            }

            main {
                padding: 40px 0;
            }

            nav {
                padding: 16px 0;
            }

            .logo {
                font-size: 24px;
            }

            .logo i {
                font-size: 28px;
            }
        }

        @media (max-width: 480px) {
            .form-container {
                padding: 32px 24px;
            }

            .form-title {
                font-size: 24px;
            }
        }

        /* Animaciones adicionales */
        .form-container,
        .alert {
            animation: fadeInUp 0.6s ease-out;
        }

        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        /* Estados de loading para el botón */
        .btn:disabled {
            opacity: 0.7;
            cursor: not-allowed;
            transform: none;
        }

        .btn:disabled:hover {
            transform: none;
            box-shadow: none;
        }
    </style>
</head>
<body>
    <header>
        <div class="container">
            <nav>
                <a href="#" class="logo">
                    <i class="fas fa-tint"></i>
                    <span>AquaSolution</span>
                </a>
            </nav>
        </div>
    </header>

    <main>
        <div class="container">
            <div class="form-container">
                <div class="admin-icon">
                    <i class="fas fa-user-shield"></i>
                </div>
                <h1 class="form-title">Panel de Administración</h1>
                <p class="form-subtitle">Accede con tus credenciales de administrador</p>
                
                <?php if (!empty($error_msg)): ?>
                    <div class="alert alert-error">
                        <i class="fas fa-exclamation-triangle"></i>
                        <span><?php echo htmlspecialchars($error_msg); ?></span>
                    </div>
                <?php endif; ?>
                
                <form method="POST" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
                    <div class="form-group">
                        <label for="username">Usuario Administrador</label>
                        <div class="input-wrapper">
                            <input type="text" id="username" name="username" class="form-control" 
                                   placeholder="Ingresa tu usuario de administrador" required autocomplete="username">
                            <i class="fas fa-user-cog input-icon"></i>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="password">Contraseña</label>
                        <div class="input-wrapper">
                            <input type="password" id="password" name="password" class="form-control" 
                                   placeholder="Ingresa tu contraseña" required autocomplete="current-password">
                            <i class="fas fa-lock input-icon"></i>
                        </div>
                    </div>
                    
                    <button type="submit" class="btn">
                        <i class="fas fa-sign-in-alt"></i>
                        <span>Acceder al Panel</span>
                    </button>
                    
                    <div class="divider">
                        <span>o</span>
                    </div>
                    
                    <a href="admin_registro.php" class="btn btn-secondary">
                        <i class="fas fa-user-plus"></i>
                        <span>Registrar Nuevo Administrador</span>
                    </a>
                    
                    <div class="form-footer">
                        <a href="login.php">
                            <i class="fas fa-arrow-left"></i>
                            <span>Volver al login de usuario</span>
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </main>

    <footer>
        <div class="container">
            <div class="footer-content">
                <div class="footer-column">
                    <h3>AquaSolution</h3>
                    <p>Líder en desarrollo de soluciones sostenibles e innovadoras para el manejo responsable del agua y recursos hídricos.</p>
                </div>
                <div class="footer-column">
                    <h3>Enlaces Rápidos</h3>
                    <ul>
                        <li><a href="index.html"><i class="fas fa-home"></i> Inicio</a></li>
                        <li><a href="#"><i class="fas fa-cogs"></i> Servicios</a></li>
                        <li><a href="#"><i class="fas fa-users"></i> Sobre Nosotros</a></li>
                        <li><a href="#"><i class="fas fa-envelope"></i> Contacto</a></li>
                    </ul>
                </div>
                <div class="footer-column">
                    <h3>Contacto</h3>
                    <ul>
                        <li><a href="mailto:info@aquasolution.com"><i class="fas fa-envelope"></i> info@aquasolution.com</a></li>
                        <li><a href="tel:+1234567890"><i class="fas fa-phone"></i> +1 234 567 890</a></li>
                        <li><a href="#"><i class="fas fa-map-marker-alt"></i> Calle Principal 123, Ciudad</a></li>
                        <li><a href="#"><i class="fas fa-clock"></i> Lun - Vie: 8:00 - 18:00</a></li>
                    </ul>
                </div>
            </div>
            <div class="copyright">
                <p>&copy; 2025 AquaSolution. Todos los derechos reservados. | Diseñado con <i class="fas fa-heart" style="color: #00b4d8;"></i> para un futuro sostenible</p>
            </div>
        </div>
    </footer>

    <script>
        // Mejorar la experiencia de usuario con JavaScript
        document.addEventListener('DOMContentLoaded', function() {
            // Animación suave para los inputs al hacer focus
            const inputs = document.querySelectorAll('.form-control');
            inputs.forEach(input => {
                input.addEventListener('focus', function() {
                    this.parentElement.classList.add('focused');
                });
                
                input.addEventListener('blur', function() {
                    this.parentElement.classList.remove('focused');
                });
            });

            // Validación en tiempo real (opcional)
            const form = document.querySelector('form');
            const submitBtn = document.querySelector('.btn[type="submit"]');
            
            form.addEventListener('submit', function(e) {
                submitBtn.disabled = true;
                submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> <span>Verificando...</span>';
                
                // Restaurar el botón después de 3 segundos si no hay redirección
                setTimeout(() => {
                    submitBtn.disabled = false;
                    submitBtn.innerHTML = '<i class="fas fa-sign-in-alt"></i> <span>Acceder al Panel</span>';
                }, 3000);
            });
        });
    </script>
</body>
</html>