
<?php
session_start();

date_default_timezone_set("America/Costa_Rica");

require_once "config/database.php";

// Token de seguridad para el formulario
if (empty($_SESSION["token_marcacion"])) {
    $_SESSION["token_marcacion"] = bin2hex(random_bytes(32));
}

// Mensajes de la marcación anterior
$mensaje = $_SESSION["mensaje_marcacion"] ?? null;
unset($_SESSION["mensaje_marcacion"]);

function escapar($valor)
{
    return htmlspecialchars(
        (string)($valor ?? ""),
        ENT_QUOTES,
        "UTF-8"
    );
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $cedula = trim($_POST["cedula"] ?? "");
    $codigo = trim($_POST["codigo_acceso"] ?? "");
    $token = $_POST["token"] ?? "";

    if (
        !is_string($token) ||
        !hash_equals($_SESSION["token_marcacion"], $token)
    ) {
        $_SESSION["mensaje_marcacion"] = [
            "tipo" => "danger",
            "texto" => "La solicitud expiró. Inténtalo nuevamente."
        ];
    } elseif ($cedula === "" || $codigo === "") {
        $_SESSION["mensaje_marcacion"] = [
            "tipo" => "warning",
            "texto" => "Completa la cédula y el PIN de acceso."
        ];
    } elseif (!preg_match('/^[0-9]{4}$/', $codigo)) {
        $_SESSION["mensaje_marcacion"] = [
            "tipo" => "warning",
            "texto" => "El PIN debe contener exactamente 4 dígitos."
        ];
    } else {
        try {
            // Buscar empleado activo con cédula y PIN correctos
            $sql = "SELECT id, nombre
                    FROM empleados
                    WHERE cedula = :cedula
                      AND codigo_acceso = :codigo
                      AND estado = 'Activo'
                    LIMIT 1";

            $stmt = $conexion->prepare($sql);
            $stmt->execute([
                ":cedula" => $cedula,
                ":codigo" => $codigo
            ]);

            $empleado = $stmt->fetch();

            if (!$empleado) {
                $_SESSION["mensaje_marcacion"] = [
                    "tipo" => "danger",
                    "texto" => "Cédula o PIN incorrecto."
                ];
            } else {
                $fecha = date("Y-m-d");
                $hora = date("H:i:s");

                $conexion->beginTransaction();

                // Consultar la asistencia del empleado para hoy
                $sql = "SELECT id, hora_entrada, hora_salida
                        FROM asistencias
                        WHERE empleado_id = :empleado_id
                          AND fecha = :fecha
                        FOR UPDATE";

                $stmt = $conexion->prepare($sql);
                $stmt->execute([
                    ":empleado_id" => $empleado["id"],
                    ":fecha" => $fecha
                ]);

                $asistencia = $stmt->fetch();

                if (!$asistencia) {
                    // Primera marcación: entrada
                    $sql = "INSERT INTO asistencias
                            (empleado_id, fecha, hora_entrada)
                            VALUES (:empleado_id, :fecha, :hora)";

                    $stmt = $conexion->prepare($sql);
                    $stmt->execute([
                        ":empleado_id" => $empleado["id"],
                        ":fecha" => $fecha,
                        ":hora" => $hora
                    ]);

                    $tipo = "Entrada";
                    $texto = "Entrada registrada correctamente.";

                } elseif (empty($asistencia["hora_entrada"])) {
                    // Completar una entrada vacía
                    $sql = "UPDATE asistencias
                            SET hora_entrada = :hora
                            WHERE id = :id";

                    $stmt = $conexion->prepare($sql);
                    $stmt->execute([
                        ":hora" => $hora,
                        ":id" => $asistencia["id"]
                    ]);

                    $tipo = "Entrada";
                    $texto = "Entrada registrada correctamente.";

                } elseif (empty($asistencia["hora_salida"])) {
                    // Segunda marcación: salida
                    $sql = "UPDATE asistencias
                            SET hora_salida = :hora
                            WHERE id = :id
                              AND hora_salida IS NULL";

                    $stmt = $conexion->prepare($sql);
                    $stmt->execute([
                        ":hora" => $hora,
                        ":id" => $asistencia["id"]
                    ]);

                    if ($stmt->rowCount() !== 1) {
                        throw new RuntimeException(
                            "La marcación ya fue procesada."
                        );
                    }

                    $tipo = "Salida";
                    $texto = "Salida registrada correctamente.";

                } else {
                    $tipo = null;
                    $texto = "Ya registraste entrada y salida para hoy.";
                }

                $conexion->commit();

                if ($tipo !== null) {
                    $_SESSION["mensaje_marcacion"] = [
                        "tipo" => "success",
                        "texto" => $texto,
                        "nombre" => $empleado["nombre"],
                        "fecha" => date("d/m/Y"),
                        "hora" => date("h:i:s A"),
                        "tipo_marcacion" => $tipo
                    ];
                } else {
                    $_SESSION["mensaje_marcacion"] = [
                        "tipo" => "warning",
                        "texto" => $texto
                    ];
                }
            }
        } catch (Throwable $e) {
            if ($conexion->inTransaction()) {
                $conexion->rollBack();
            }

            error_log($e->getMessage());

            $_SESSION["mensaje_marcacion"] = [
                "tipo" => "danger",
                "texto" => "No fue posible completar la marcación. Inténtalo nuevamente."
            ];
        }
    }

    // Evitar reenviar el formulario al actualizar
    header("Location: marcacion.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>Control de asistencia | Marcación</title>

    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
        rel="stylesheet"
    >
    <link rel="stylesheet" href="css/marcacion.css">

</head>

<body>

<nav class="navbar navbar-dark shadow-sm">
    <div class="container">
        <a href="index.php" class="navbar-brand fw-semibold text-decoration-none">
            Control de asistencia
        </a>

    <div class="d-flex gap-2">
    <a href="index.php" class="btn btn-outline-light btn-sm">
        Volver al inicio
    </a>

    <a href="login.php" class="btn btn-outline-light btn-sm">
        Acceso administrativo
    </a>
    </div>

    </div>
</nav>

<main class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-7 col-lg-5">

            <div class="card border-0 shadow">
                <div class="card-body p-4 p-md-5">

                    <div class="text-center mb-4">
                        <h2 class="fw-bold">Registrar asistencia</h2>
                        <p class="text-muted">
                            Ingresa tu cédula y PIN para marcar tu entrada o salida.
                        </p>
                    </div>

                    <?php if ($mensaje): ?>
                        <div class="alert alert-<?= escapar($mensaje["tipo"]) ?>">
                            <p class="mb-1">
                                <?= escapar($mensaje["texto"]) ?>
                            </p>

                            <?php if (!empty($mensaje["nombre"])): ?>
                                <hr>

                                <p class="mb-1">
                                    <strong>Empleado:</strong>
                                    <?= escapar($mensaje["nombre"]) ?>
                                </p>

                                <p class="mb-1">
                                    <strong>Fecha:</strong>
                                    <?= escapar($mensaje["fecha"]) ?>
                                </p>

                                <p class="mb-0">
                                    <strong>
                                        <?= escapar($mensaje["tipo_marcacion"]) ?>:
                                    </strong>
                                    <?= escapar($mensaje["hora"]) ?>
                                </p>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>

                    <form method="POST" action="marcacion.php" autocomplete="off">
                        <input
                            type="hidden"
                            name="token"
                            value="<?= escapar($_SESSION["token_marcacion"]) ?>"
                        >

                        <div class="mb-3">
                            <label for="cedula" class="form-label">
                                Cédula
                            </label>

                            <input
                                type="text"
                                id="cedula"
                                name="cedula"
                                class="form-control form-control-lg"
                                placeholder="Ingresa tu cédula"
                                maxlength="30"
                                autocomplete="off"
                                required
                                autofocus
                            >
                        </div>

                        <div class="mb-4">
                            <label for="codigo_acceso" class="form-label">
                                PIN de acceso
                            </label>

                            <input
                                type="password"
                                id="codigo_acceso"
                                name="codigo_acceso"
                                class="form-control form-control-lg"
                                placeholder="4 dígitos"
                                inputmode="numeric"
                                pattern="[0-9]{4}"
                                minlength="4"
                                maxlength="4"
                                autocomplete="new-password"
                                required
                            >

                            <div class="form-text">
                                Ingresa tu PIN numérico de 4 dígitos.
                            </div>
                        </div>

                        <button
                            type="submit"
                            class="btn btn-primary btn-lg w-100"
                        >
                            Registrar
                        </button>
                    </form>

                    <p class="text-center text-muted small mt-4 mb-0">
                        Utiliza tu cédula y el PIN asignado por la administración.
                    </p>

                </div>
            </div>

            <p class="text-center text-muted small mt-3">
                Sistema de control de asistencia
            </p>

        </div>
    </div>
</main>

</body>
</html>
