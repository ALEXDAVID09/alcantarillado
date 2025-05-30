<?php
// Configuración de la base de datos (código PHP permanece igual)
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
$success_msg = '';

try {
    $pdo = new PDO($dsn, $username, $password, $options);
    
    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        $nombre = $_POST['nombre'] ?? '';
        $email = $_POST['email'] ?? '';
        $password = $_POST['password'] ?? '';
        $confirm_password = $_POST['confirm_password'] ?? '';
        
        if (empty($nombre) || empty($email) || empty($password) || empty($confirm_password)) {
            $error_msg = "Por favor, complete todos los campos.";
        } elseif ($password !== $confirm_password) {
            $error_msg = "Las contraseñas no coinciden.";
        } else {
            // Verificar si el email ya existe
            $stmt = $pdo->prepare("SELECT id FROM usuarios WHERE email = ?");
            $stmt->execute([$email]);
            
            if ($stmt->fetch()) {
                $error_msg = "Este correo electrónico ya está registrado.";
            } else {
                // Hash de la contraseña
                $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                
                // Insertar nuevo usuario
                $stmt = $pdo->prepare("INSERT INTO usuarios (nombre, email, password) VALUES (?, ?, ?)");
                $stmt->execute([$nombre, $email, $hashed_password]);
                
                $success_msg = "Registro exitoso. Ahora puedes iniciar sesión.";
                header("Refresh: 3; url=login.php");
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
    <title>Registro - AquaSolution Quibdó</title>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Montserrat:wght@300;400;500;600;700&family=Open+Sans:wght@300;400;600&display=swap">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        :root {
            --color-primario: #005f87; /* Azul más oscuro y profesional */
            --color-secundario: #0088a9; /* Azul intermedio */
            --color-acento: #00b4d8; /* Azul claro */
            --color-fondo: #f8fbfc; /* Fondo muy claro casi blanco */
            --color-texto: #2d3748; /* Texto oscuro para mejor legibilidad */
            --color-exito: #38a169; /* Verde para mensajes de éxito */
            --color-error: #e53e3e; /* Rojo para errores */
            --color-borde: #e2e8f0; /* Borde suave */
            --sombra: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
            --sombra-hover: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
            --transicion: all 0.3s ease;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Montserrat', sans-serif;
            background-color: var(--color-fondo);
            color: var(--color-texto);
            line-height: 1.6;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }

        .container {
            width: 90%;
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }

        /* Header mejorado */
        header {
            background-color: white;
            box-shadow: var(--sombra);
            position: sticky;
            top: 0;
            z-index: 100;
        }

        nav {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 15px 0;
        }

        .logo {
            font-size: 24px;
            font-weight: 700;
            color: var(--color-primario);
            display: flex;
            align-items: center;
        }

        .logo i {
            margin-right: 10px;
            color: var(--color-acento);
        }

        .nav-links {
            display: flex;
            list-style: none;
        }

        .nav-links li {
            margin-left: 25px;
        }

        .nav-links a {
            color: var(--color-texto);
            text-decoration: none;
            font-weight: 500;
            padding: 8px 12px;
            border-radius: 4px;
            transition: var(--transicion);
            position: relative;
        }

        .nav-links a:hover {
            color: var(--color-primario);
        }

        .nav-links a::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 0;
            width: 0;
            height: 2px;
            background-color: var(--color-acento);
            transition: var(--transicion);
        }

        .nav-links a:hover::after {
            width: 100%;
        }

        /* Main content */
        main {
            flex: 1;
            display: flex;
            align-items: center;
            padding: 40px 0;
        }

        /* Form container mejorado */
        .form-container {
            background-color: white;
            border-radius: 12px;
            padding: 40px;
            box-shadow: var(--sombra);
            width: 100%;
            max-width: 500px;
            margin: 0 auto;
            border: 1px solid var(--color-borde);
            transition: var(--transicion);
        }

        .form-container:hover {
            box-shadow: var(--sombra-hover);
        }

        .form-title {
            text-align: center;
            margin-bottom: 30px;
            color: var(--color-primario);
            font-size: 28px;
            font-weight: 600;
            position: relative;
            padding-bottom: 10px;
        }

        .form-title::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 50%;
            transform: translateX(-50%);
            width: 60px;
            height: 3px;
            background-color: var(--color-acento);
        }

        .form-group {
            margin-bottom: 25px;
            position: relative;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 500;
            color: var(--color-texto);
            font-size: 14px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .form-control {
            width: 100%;
            padding: 14px 15px;
            border: 1px solid var(--color-borde);
            border-radius: 6px;
            font-size: 16px;
            transition: var(--transicion);
            background-color: var(--color-fondo);
        }

        .form-control:focus {
            border-color: var(--color-acento);
            outline: none;
            box-shadow: 0 0 0 3px rgba(0, 180, 216, 0.1);
        }

        .btn {
            display: inline-block;
            background-color: var(--color-primario);
            color: white;
            padding: 14px 20px;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            text-decoration: none;
            font-weight: 600;
            font-size: 16px;
            transition: var(--transicion);
            text-align: center;
            width: 100%;
        }

        .btn:hover {
            background-color: var(--color-secundario);
            transform: translateY(-2px);
            box-shadow: var(--sombra-hover);
        }

        .form-footer {
            text-align: center;
            margin-top: 25px;
            font-size: 14px;
            color: #718096;
        }

        .form-footer a {
            color: var(--color-primario);
            text-decoration: none;
            font-weight: 500;
            transition: var(--transicion);
        }

        .form-footer a:hover {
            color: var(--color-secundario);
            text-decoration: underline;
        }

        /* Alertas mejoradas */
        .alert {
            padding: 15px;
            border-radius: 6px;
            margin-bottom: 20px;
            font-size: 14px;
            display: flex;
            align-items: center;
        }

        .alert-error {
            background-color: #fff5f5;
            color: var(--color-error);
            border-left: 4px solid var(--color-error);
        }

        .alert-success {
            background-color: #f0fff4;
            color: var(--color-exito);
            border-left: 4px solid var(--color-exito);
        }

        .alert i {
            margin-right: 10px;
            font-size: 18px;
        }

        /* Footer mejorado */
        footer {
            background-color: var(--color-primario);
            color: white;
            padding: 40px 0 20px;
            margin-top: 60px;
        }

        .footer-content {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 30px;
            margin-bottom: 30px;
        }

        .footer-column h3 {
            margin-bottom: 20px;
            font-size: 18px;
            font-weight: 600;
            position: relative;
            padding-bottom: 10px;
        }

        .footer-column h3::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 0;
            width: 40px;
            height: 2px;
            background-color: var(--color-acento);
        }

        .footer-column ul {
            list-style: none;
        }

        .footer-column ul li {
            margin-bottom: 12px;
        }

        .footer-column a {
            color: rgba(255, 255, 255, 0.8);
            text-decoration: none;
            transition: var(--transicion);
            display: flex;
            align-items: center;
        }

        .footer-column a:hover {
            color: white;
            transform: translateX(5px);
        }

        .footer-column a i {
            margin-right: 8px;
            font-size: 14px;
        }

        .copyright {
            text-align: center;
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid rgba(255, 255, 255, 0.1);
            font-size: 14px;
            color: rgba(255, 255, 255, 0.7);
        }

        /* Efectos de iconos en inputs */
        .input-with-icon {
            position: relative;
        }

        .input-with-icon i {
            position: absolute;
            left: 15px;
            top: 50%;
            transform: translateY(-50%);
            color: #a0aec0;
        }

        .input-with-icon .form-control {
            padding-left: 45px;
        }

        /* Responsive */
        @media (max-width: 768px) {
            .nav-links {
                display: none;
            }

            .form-container {
                padding: 30px 20px;
            }

            .form-title {
                font-size: 24px;
            }
        }
    </style>
</head>
<body>
    <header>
        <div class="container">
            <nav>
                <div class="logo">
                    <i class="fas fa-tint"></i>
                    <span>AquaSolution</span>
                </div>
                <ul class="nav-links">
                    <li><a href="sistema_agua.html#inicio">Inicio</a></li>
                    <li><a href="sistema_agua.html#servicios">Servicios</a></li>
                    <li><a href="sistema_agua.html#contacto">Contacto</a></li>
                    <li><a href="sistema_agua.html">Ver Página Principal</a></li>
                </ul>
            </nav>
        </div>
    </header>

    <main class="container">
        <div class="form-container">
            <h2 class="form-title">Crear Cuenta</h2>
            
            <?php if (!empty($error_msg)): ?>
                <div class="alert alert-error">
                    <i class="fas fa-exclamation-circle"></i>
                    <?php echo $error_msg; ?>
                </div>
            <?php endif; ?>
            
            <?php if (!empty($success_msg)): ?>
                <div class="alert alert-success">
                    <i class="fas fa-check-circle"></i>
                    <?php echo $success_msg; ?>
                </div>
            <?php endif; ?>
            
            <form method="POST" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
                <div class="form-group input-with-icon">
                    <label for="nombre">Nombre Completo</label>
                    <i class="fas fa-user"></i>
                    <input type="text" id="nombre" name="nombre" class="form-control" placeholder="Ingresa tu nombre completo" required>
                </div>
                
                <div class="form-group input-with-icon">
                    <label for="email">Correo Electrónico</label>
                    <i class="fas fa-envelope"></i>
                    <input type="email" id="email" name="email" class="form-control" placeholder="tu@email.com" required>
                </div>
                
                <div class="form-group input-with-icon">
                    <label for="password">Contraseña</label>
                    <i class="fas fa-lock"></i>
                    <input type="password" id="password" name="password" class="form-control" placeholder="Crea una contraseña segura" required>
                </div>
                
                <div class="form-group input-with-icon">
                    <label for="confirm_password">Confirmar Contraseña</label>
                    <i class="fas fa-lock"></i>
                    <input type="password" id="confirm_password" name="confirm_password" class="form-control" placeholder="Repite tu contraseña" required>
                </div>
                
                <button type="submit" class="btn">Registrarse</button>
                
                <div class="form-footer">
                    <p>¿Ya tienes una cuenta? <a href="login.php">Inicia Sesión</a></p>
                </div>
            </form>
        </div>
    </main>

    <footer>
        <div class="container">
            <div class="footer-content">
                <div class="footer-column">
                    <h3>AquaSolution Quibdó</h3>
                    <p>Dedicados a desarrollar soluciones sostenibles para el manejo del agua en la región del Chocó.</p>
                </div>
                <div class="footer-column">
                    <h3>Enlaces Rápidos</h3>
                    <ul>
                        <li><a href="sistema_agua.html#inicio"><i class="fas fa-chevron-right"></i> Inicio</a></li>
                        <li><a href="sistema_agua.html#servicios"><i class="fas fa-chevron-right"></i> Servicios</a></li>
                        <li><a href="sistema_agua.html#contacto"><i class="fas fa-chevron-right"></i> Contacto</a></li>
                        <li><a href="sistema_agua.html"><i class="fas fa-chevron-right"></i> Página Principal</a></li>
                    </ul>
                </div>
                <div class="footer-column">
                    <h3>Contacto</h3>
                    <ul>
                        <li><a href="mailto:info@aquasolution.com"><i class="fas fa-envelope"></i> info@aquasolution.com</a></li>
                        <li><a href="tel:+5746711234"><i class="fas fa-phone"></i> (4) 671-1234</a></li>
                        <li><a href="#"><i class="fas fa-map-marker-alt"></i> Carrera 1 # 25-08, Quibdó</a></li>
                    </ul>
                </div>
            </div>
            <div class="copyright">
                <p>&copy; 2025 AquaSolution Quibdó. Todos los derechos reservados.</p>
                <p style="margin-top: 10px; font-size: 12px;">
                    🎓 Proyecto desarrollado por <strong>ALEX DAVID GARCÍA CHALA</strong> - 5° Semestre
                </p>
            </div>
        </div>
    </footer>
</body>
</html>