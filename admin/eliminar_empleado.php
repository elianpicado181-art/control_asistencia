
<?php
require_once "../config/auth.php";
require_once "../config/database.php";

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: empleados.php");
    exit;
}

$id = filter_input(INPUT_POST, "id", FILTER_VALIDATE_INT);

if (!$id || $id < 1) {
    header("Location: empleados.php?error=id");
    exit;
}

try {
    // Verificar que el empleado exista.
    $stmt = $conexion->prepare(
        "SELECT id FROM empleados WHERE id = :id"
    );
    $stmt->execute([":id" => $id]);

    if (!$stmt->fetch()) {
        header("Location: empleados.php?error=no_encontrado");
        exit;
    }

    // Comprobar si tiene historial de asistencia.
    $stmt = $conexion->prepare(
        "SELECT COUNT(*) FROM asistencias WHERE empleado_id = :id"
    );
    $stmt->execute([":id" => $id]);

    if ((int) $stmt->fetchColumn() > 0) {
        header("Location: empleados.php?error=tiene_asistencias");
        exit;
    }

    // Eliminar solamente si no tiene registros de asistencia.
    $stmt = $conexion->prepare(
        "DELETE FROM empleados WHERE id = :id"
    );
    $stmt->execute([":id" => $id]);

    header("Location: empleados.php?eliminado=1");
    exit;

} catch (PDOException $e) {
    error_log($e->getMessage());

    header("Location: empleados.php?error=bd");
    exit;
}
