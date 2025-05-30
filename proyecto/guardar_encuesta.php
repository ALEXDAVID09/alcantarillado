<?php
include 'conexion.php';

$sector = $_POST['sector_sin_acueducto'] ?? '';
$tiene_acueducto = $_POST['tiene_acueducto'] ?? '';
$tiene_alcantarillado = $_POST['tiene_alcantarillado'] ?? '';
$consumo_agua = $_POST['consumo_agua'] ?? '';

$fuente_agua = isset($_POST['fuente_agua']) ? (is_array($_POST['fuente_agua']) ? implode(', ', $_POST['fuente_agua']) : $_POST['fuente_agua']) : '';
$problemas = isset($_POST['problemas']) ? (is_array($_POST['problemas']) ? implode(', ', $_POST['problemas']) : $_POST['problemas']) : '';
$sugerencias = isset($_POST['sugerencias']) ? (is_array($_POST['sugerencias']) ? implode(', ', $_POST['sugerencias']) : $_POST['sugerencias']) : '';

$user_id = 1;
$fecha = date('Y-m-d');

try {
    $stmt = $conexion->prepare("INSERT INTO encuestas (
        user_id, sector_sin_acueducto, tiene_acueducto, tiene_alcantarillado,
        consumo_agua, fuente_agua, problemas, sugerencias, fecha_encuesta
    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");

    $stmt->execute([
        $user_id, $sector, $tiene_acueducto, $tiene_alcantarillado,
        $consumo_agua, $fuente_agua, $problemas, $sugerencias, $fecha
    ]);

    echo "<script>
        alert('✅ Encuesta guardada correctamente.');
        window.location.href = 'graficas.php';
    </script>";
} catch (PDOException $e) {
    echo "❌ Error al guardar la encuesta: " . $e->getMessage();
}
?>
