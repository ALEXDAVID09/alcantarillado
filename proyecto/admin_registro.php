<?php
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
$success_msg = '';

try {
    $pdo = new PDO($dsn, $username, $password, $options);
    
    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        $nombre = $_POST['nombre'] ?? '';
        $username = $_POST['username'] ?? '';
        $password = $_POST['password'] ?? '';
        $confirm_password = $_POST['confirm_password'] ?? '';
        $nivel_acceso = $_POST['nivel_acceso'] ?? 1;
        
        if (empty($nombre) || empty($username) || empty($password) || empty($confirm_password)) {
            $error_msg = "Por favor, complete todos los campos.";
        } elseif ($password !== $confirm_password) {
            $error_msg = "Las contraseñas no coinciden.";
        } elseif (strlen($password) < 8) {
            $error_msg = "La contraseña debe tener al menos 8 caracteres.";
        } else {
            // Verificar si el usuario ya existe
            $stmt = $pdo->prepare("SELECT id FROM administradores WHERE username = ?");
            $stmt->execute([$username]);
            
            if ($stmt->fetch()) {
                $error_msg = "Este nombre de usuario ya está registrado.";
            } else {
                // Hash de la contraseña
                $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                
                // Insertar nuevo administrador
                $stmt = $pdo->prepare("INSERT INTO administradores (nombre, username, password, nivel_acceso) VALUES (?, ?, ?, ?)");
                $stmt->execute([$nombre, $username, $hashed_password, $nivel_acceso]);
                
                $success_msg = "Administrador registrado exitosamente.";
                header("Refresh: 3; url=admin_login.php");
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
    <title>Registro de Administrador - AquaSolution</title>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Montserrat:wght@300;400;500;600;700&family=Open+Sans:wght@300;400;600&display=swap">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        :root {
            --color-primario: #005f87;
            --color-secundario: #0088a9;
            --color-acento: #00b4d8;
            --color-fondo: #f8fbfc;
            --color-texto: #2d3748;
            --color-exito: #38a169;
            --color-error: #e53e3e;
            --color-borde: #e2e8f0;
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
            background-image: linear-gradient(rgba(248, 251, 252, 0.95), rgba(248, 251, 252, 0.95)), url('https://images.unsplash.com/photo-1569335468888-1e3360f0d2e3?ixlib=rb-1.2.1&auto=format&fit=crop&w=1350&q=80');
            background-size: cover;
            background-position: center;
            background-attachment: fixed;
        }

        .container {
            width: 90%;
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }

        /* Header mejorado */
        header {
            background-color: rgba(255, 255, 255, 0.95);
            box-shadow: var(--sombra);
            position: sticky;
            top: 0;
            z-index: 100;
            backdrop-filter: blur(5px);
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

        /* Main content */
        main {
            flex: 1;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 40px 0;
        }

        /* Form container mejorado */
        .form-container {
            background-color: rgba(255, 255, 255, 0.95);
            border-radius: 12px;
            padding: 40px;
            box-shadow: var(--sombra);
            width: 100%;
            max-width: 600px;
            margin: 0 auto;
            border: 1px solid var(--color-borde);
            transition: var(--transicion);
            backdrop-filter: blur(5px);
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

        .admin-icon {
            text-align: center;
            margin-bottom: 20px;
            color: var(--color-primario);
        }

        .admin-icon i {
            font-size: 50px;
            background-color: rgba(0, 180, 216, 0.1);
            padding: 20px;
            border-radius: 50%;
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
            background-color: rgba(248, 251, 252, 0.7);
        }

        .form-control:focus {
            border-color: var(--color-acento);
            outline: none;
            box-shadow: 0 0 0 3px rgba(0, 180, 216, 0.1);
        }

        .form-select {
            width: 100%;
            padding: 14px 15px;
            border: 1px solid var(--color-borde);
            border-radius: 6px;
            font-size: 16px;
            transition: var(--transicion);
            background-color: rgba(248, 251, 252, 0.7);
            appearance: none;
            background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 20 20'%3e%3cpath stroke='%236b7280' stroke-linecap='round' stroke-linejoin='round' stroke-width='1.5' d='M6 8l4 4 4-4'/%3e%3c/svg%3e");
            background-position: right 0.5rem center;
            background-repeat: no-repeat;
            background-size: 1.5em 1.5em;
        }

        .form-select:focus {
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
            display: inline-flex;
            align-items: center;
        }

        .form-footer a:hover {
            color: var(--color-secundario);
            text-decoration: underline;
        }

        .form-footer a i {
            margin-right: 8px;
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

        /* Password strength */
        .password-strength {
            margin-top: 8px;
            height: 4px;
            background-color: #e2e8f0;
            border-radius: 2px;
            overflow: hidden;
        }

        .strength-meter {
            height: 100%;
            width: 0;
            transition: width 0.3s ease;
        }

        .weak {
            background-color: #e53e3e;
            width: 33%;
        }

        .medium {
            background-color: #ed8936;
            width: 66%;
        }

        .strong {
            background-color: #38a169;
            width: 100%;
        }

        .password-hint {
            font-size: 12px;
            color: #718096;
            margin-top: 5px;
        }

        /* Footer mejorado */
        footer {
            background-color: var(--color-primario);
            color: white;
            padding: 40px 0 20px;
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

        /* Responsive */
        @media (max-width: 768px) {
            .form-container {
                padding: 30px 20px;
            }

            .form-title {
                font-size: 24px;
            }

            .admin-icon i {
                font-size: 40px;
                padding: 15px;
            }
        }
    </style>
    <script>
        function checkPasswordStrength() {
            const password = document.getElementById('password').value;
            const strengthMeter = document.getElementById('strength-meter');
            const hint = document.getElementById('password-hint');
            
            // Reset
            strengthMeter.className = 'strength-meter';
            hint.textContent = '';
            
            if (!password) return;
            
            // Check password strength
            const hasLetters = /[a-zA-Z]/.test(password);
            const hasNumbers = /[0-9]/.test(password);
            const hasSpecial = /[^a-zA-Z0-9]/.test(password);
            let strength = 0;
            
            if (password.length >= 8) strength++;
            if (password.length >= 12) strength++;
            if (hasLetters && hasNumbers) strength++;
            if (hasSpecial) strength++;
            if (hasLetters && hasNumbers && hasSpecial && password.length >= 12) strength++;
            
            // Update UI
            if (strength <= 2) {
                strengthMeter.classList.add('weak');
                hint.textContent = 'Contraseña débil - usa más caracteres, números y símbolos';
            } else if (strength <= 4) {
                strengthMeter.classList.add('medium');
                hint.textContent = 'Contraseña moderada - podría ser más fuerte';
            } else {
                strengthMeter.classList.add('strong');
                hint.textContent = 'Contraseña fuerte';
            }
        }
        
        function validatePasswords() {
            const password = document.getElementById('password').value;
            const confirmPassword = document.getElementById('confirm_password').value;
            const submitBtn = document.getElementById('submit-btn');
            
            if (password && confirmPassword && password !== confirmPassword) {
                document.getElementById('password-match').textContent = 'Las contraseñas no coinciden';
                submitBtn.disabled = true;
            } else {
                document.getElementById('password-match').textContent = '';
                submitBtn.disabled = false;
            }
        }
    </script>
</head>
<body>
    <header>
        <div class="container">
            <nav>
                <div class="logo">
                    <i class="fas fa-tint"></i>
                    <span>AquaSolution</span>
                </div>
            </nav>
        </div>
    </header>

    <main class="container">
        <div class="form-container">
            <div class="admin-icon">
                <i class="fas fa-user-shield"></i>
            </div>
            <h2 class="form-title">Registro de Administrador</h2>
            
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
                    <label for="username">Nombre de Usuario</label>
                    <i class="fas fa-user-tag"></i>
                    <input type="text" id="username" name="username" class="form-control" placeholder="Crea un nombre de usuario" required>
                </div>
                
                <div class="form-group input-with-icon">
                    <label for="password">Contraseña</label>
                    <i class="fas fa-lock"></i>
                    <input type="password" id="password" name="password" class="form-control" placeholder="Crea una contraseña segura" 
                           required minlength="8" onkeyup="checkPasswordStrength()">
                    <div class="password-strength">
                        <div id="strength-meter" class="strength-meter"></div>
                    </div>
                    <div id="password-hint" class="password-hint"></div>
                </div>
                
                <div class="form-group input-with-icon">
                    <label for="confirm_password">Confirmar Contraseña</label>
                    <i class="fas fa-lock"></i>
                    <input type="password" id="confirm_password" name="confirm_password" class="form-control" 
                           placeholder="Repite tu contraseña" required minlength="8" onkeyup="validatePasswords()">
                    <div id="password-match" class="password-hint" style="color: var(--color-error);"></div>
                </div>
                
                <div class="form-group">
                    <label for="nivel_acceso">Nivel de Acceso</label>
                    <select id="nivel_acceso" name="nivel_acceso" class="form-select" required>
                        <option value="1">Administrador Básico</option>
                        <option value="2">Administrador Avanzado</option>
                        <option value="3">Super Administrador</option>
                    </select>
                </div>
                
                <button type="submit" id="submit-btn" class="btn">
                    <i class="fas fa-user-plus"></i> Registrar Administrador
                </button>
                
                <div class="form-footer">
                    <p><a href="admin_login.php"><i class="fas fa-sign-in-alt"></i> ¿Ya tienes una cuenta? Inicia sesión</a></p>
                </div>
            </form>
        </div>
    </main>

    <footer>
        <div class="container">
            <div class="footer-content">
                <div class="footer-column">
                    <h3>AquaSolution</h3>
                    <p>Dedicados a desarrollar soluciones sostenibles para el manejo del agua.</p>
                </div>
                <div class="footer-column">
                    <h3>Enlaces Rápidos</h3>
                    <ul>
                        <li><a href="index.html"><i class="fas fa-chevron-right"></i> Inicio</a></li>
                        <li><a href="#"><i class="fas fa-chevron-right"></i> Servicios</a></li>
                        <li><a href="#"><i class="fas fa-chevron-right"></i> Sobre Nosotros</a></li>
                        <li><a href="#"><i class="fas fa-chevron-right"></i> Contacto</a></li>
                    </ul>
                </div>
                <div class="footer-column">
                    <h3>Contacto</h3>
                    <ul>
                        <li><a href="mailto:info@aquasolution.com"><i class="fas fa-envelope"></i> info@aquasolution.com</a></li>
                        <li><a href="tel:+1234567890"><i class="fas fa-phone"></i> +1 234 567 890</a></li>
                        <li><a href="#"><i class="fas fa-map-marker-alt"></i> Calle Principal 123</a></li>
                    </ul>
                </div>
            </div>
            <div class="copyright">
                <p>&copy; 2025 AquaSolution. Todos los derechos reservados.</p>
            </div>
        </div>
    </footer>
</body>
</html>