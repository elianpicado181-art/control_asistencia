
<?php
require_once "../config/auth.php";
require_once "../config/database.php";

$id = filter_input(INPUT_GET, "id", FILTER_VALIDATE_INT);

if (!$id || $id < 1) {
    header("Location: empleados.php");
    exit;
}

$stmt = $conexion->prepare(
    "SELECT * FROM empleados WHERE id = :id"
);
$stmt->execute([":id" => $id]);
$empleado = $stmt->fetch();

if (!$empleado) {
    header("Location: empleados.php");
    exit;
}

$error = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $cedula = trim($_POST["cedula"] ?? "");
    $nombre = trim($_POST["nombre"] ?? "");
    $correo = trim($_POST["correo"] ?? "");
    $telefono = trim($_POST["telefono"] ?? "");
    $estado = $_POST["estado"] ?? "";

    if ($cedula === "" || $nombre === "") {
        $error = "La cédula y el nombre son obligatorios.";
    } elseif (
        $correo !== "" &&
        !filter_var($correo, FILTER_VALIDATE_EMAIL)
    ) {
        $error = "El correo electrónico no es válido.";
    } elseif (!in_array($estado, ["Activo", "Inactivo"], true)) {
        $error = "El estado seleccionado no es válido.";
    } else {
        try {
            $sql = "UPDATE empleados
                    SET cedula = :cedula,
                        nombre = :nombre,
                        correo = :correo,
                        telefono = :telefono,
                        estado = :estado
                    WHERE id = :id";

            $stmt = $conexion->prepare($sql);
            $stmt->execute([
                ":cedula" => $cedula,
                ":nombre" => $nombre,
                ":correo" => $correo !== "" ? $correo : null,
                ":telefono" => $telefono !== "" ? $telefono : null,
                ":estado" => $estado,
                ":id" => $id
            ]);

            header("Location: empleados.php?actualizado=1");
            exit;

        } catch (PDOException $e) {
            if ($e->getCode() === "23000") {
                $error = "Esa cédula ya pertenece a otro empleado.";
            } else {
                error_log($e->getMessage());
                $error = "No se pudo actualizar el empleado.";
            }
        }
    }

    $empleado["cedula"] = $cedula;
    $empleado["nombre"] = $nombre;
    $empleado["correo"] = $correo;
    $empleado["telefono"] = $telefono;
    $empleado["estado"] = $estado;
}

function escapar($valor) {
    return htmlspecialchars((string)($valor ?? ""), ENT_QUOTES, "UTF-8");
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Editar empleado</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
          rel="stylesheet">
</head>
<body class="bg-light">

<nav class="navbar navbar-dark bg-primary">
    <div class="container">
        <a href="index.php" class="navbar-brand">Control de asistencia</a>
    </div>
</nav>

<div class="container py-4">
    <div class="row justify-content-center">
        <div class="col-lg-7">
            <div class="card border-0 shadow-sm">
                <div class="card-body p-4">
                    <h2 class="mb-4">Editar empleado</h2>

                    <?php if ($error !== ""): ?>
                        <div class="alert alert-danger">
                            <?= escapar($error) ?>
                        </div>
                    <?php endif; ?>

                    <form method="POST">
                        <div class="mb-3">
                            <label class="form-label">Cédula *</label>
                            <input type="text" name="cedula"
                                   class="form-control" maxlength="30"
                                   required value="<?= escapar($empleado["cedula"]) ?>">
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Nombre completo *</label>
                            <input type="text" name="nombre"
                                   class="form-control" maxlength="150"
                                   required value="<?= escapar($empleado["nombre"]) ?>">
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Correo electrónico</label>
                            <input type="email" name="correo"
                                   class="form-control" maxlength="150"
                                   value="<?= escapar($empleado["correo"]) ?>">
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Teléfono</label>
                            <input type="text" name="telefono"
                                   class="form-control" maxlength="30"
                                   value="<?= escapar($empleado["telefono"]) ?>">
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Estado</label>
                            <select name="estado" class="form-select" required>
                                <option value="Activo"
                                    <?= $empleado["estado"] === "Activo" ? "selected" : "" ?>>
                                    Activo
                                </option>
                                <option value="Inactivo"
                                    <?= $empleado["estado"] === "Inactivo" ? "selected" : "" ?>>
                                    Inactivo
                                </option>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Código de acceso</label>
                            <input type="text" class="form-control"
                                   value="<?= escapar($empleado["codigo_acceso"]) ?>"
                                   readonly>
                            <div class="form-text">
                                El código no se modifica desde esta pantalla.
                            </div>
                        </div>

                        <button type="submit" class="btn btn-primary">
                            Guardar cambios
                        </button>
                        <a href="empleados.php" class="btn btn-secondary">
                            Cancelar
                        </a>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
</body>
</html>
