<?php
$host = 'localhost';
$puerto = '3309';
$base_de_datos = 'agua_db';
$usuario = 'root';
$contrasena = '';

try {
    $conexion = new PDO("mysql:host=$host;port=$puerto;dbname=$base_de_datos", $usuario, $contrasena);
    $conexion->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Error de conexión: " . $e->getMessage());
}
?>
