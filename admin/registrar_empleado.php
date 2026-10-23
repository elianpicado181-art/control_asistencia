
<?php
require_once "../config/auth.php";
require_once "../config/database.php";

$error = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $cedula = trim($_POST["cedula"] ?? "");
    $nombre = trim($_POST["nombre"] ?? "");
    $correo = trim($_POST["correo"] ?? "");
    $telefono = trim($_POST["telefono"] ?? "");

    if ($cedula === "" || $nombre === "") {
        $error = "La cédula y el nombre son obligatorios.";

    } elseif (
        $correo !== "" &&
        !filter_var($correo, FILTER_VALIDATE_EMAIL)
    ) {
        $error = "El correo electrónico no es válido.";

    } else {
        try {
            $codigo = "";

            // Generar un PIN numérico único de 4 dígitos.
            for ($i = 0; $i < 50; $i++) {
                $candidato = (string) random_int(1000, 9999);

                $consulta = $conexion->prepare(
                    "SELECT id
                     FROM empleados
                     WHERE codigo_acceso = :codigo
                     LIMIT 1"
                );

                $consulta->execute([
                    ":codigo" => $candidato
                ]);

                if (!$consulta->fetch()) {
                    $codigo = $candidato;
                    break;
                }
            }

            if ($codigo === "") {
                throw new RuntimeException(
                    "No fue posible generar un PIN único."
                );
            }

            $sql = "INSERT INTO empleados
                    (cedula, nombre, correo, telefono, codigo_acceso)
                    VALUES
                    (:cedula, :nombre, :correo, :telefono, :codigo)";

            $stmt = $conexion->prepare($sql);

            $stmt->execute([
                ":cedula" => $cedula,
                ":nombre" => $nombre,
                ":correo" => $correo !== "" ? $correo : null,
                ":telefono" => $telefono !== "" ? $telefono : null,
                ":codigo" => $codigo
            ]);

            header("Location: empleados.php?registrado=1");
            exit;

        } catch (PDOException $e) {
            if ($e->getCode() === "23000") {
                $error = "La cédula ya está registrada o existe un dato duplicado.";
            } else {
                error_log($e->getMessage());
                $error = "Ocurrió un error al guardar el empleado.";
            }

        } catch (RuntimeException $e) {
            error_log($e->getMessage());
            $error = "No se pudo generar el PIN. Inténtalo nuevamente.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>Registrar empleado</title>

    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
        rel="stylesheet"
    >
</head>

<body class="bg-light">

<nav class="navbar navbar-dark bg-primary">
    <div class="container">
        <a class="navbar-brand" href="index.php">
            Control de asistencia
        </a>
    </div>
</nav>

<div class="container py-4">
    <div class="row justify-content-center">
        <div class="col-lg-7">

            <div class="card border-0 shadow-sm">
                <div class="card-body p-4">

                    <h2 class="mb-4">Registrar empleado</h2>

                    <?php if ($error !== ""): ?>
                        <div class="alert alert-danger">
                            <?= htmlspecialchars(
                                $error,
                                ENT_QUOTES,
                                "UTF-8"
                            ) ?>
                        </div>
                    <?php endif; ?>

                    <form method="POST" autocomplete="off">

                        <div class="mb-3">
                            <label for="cedula" class="form-label">
                                Cédula *
                            </label>

                            <input
                                type="text"
                                id="cedula"
                                name="cedula"
                                class="form-control"
                                maxlength="30"
                                required
                                value="<?= htmlspecialchars(
                                    $_POST["cedula"] ?? "",
                                    ENT_QUOTES,
                                    "UTF-8"
                                ) ?>"
                            >
                        </div>

                        <div class="mb-3">
                            <label for="nombre" class="form-label">
                                Nombre completo *
                            </label>

                            <input
                                type="text"
                                id="nombre"
                                name="nombre"
                                class="form-control"
                                maxlength="150"
                                required
                                value="<?= htmlspecialchars(
                                    $_POST["nombre"] ?? "",
                                    ENT_QUOTES,
                                    "UTF-8"
                                ) ?>"
                            >
                        </div>

                        <div class="mb-3">
                            <label for="correo" class="form-label">
                                Correo electrónico
                            </label>

                            <input
                                type="email"
                                id="correo"
                                name="correo"
                                class="form-control"
                                maxlength="150"
                                value="<?= htmlspecialchars(
                                    $_POST["correo"] ?? "",
                                    ENT_QUOTES,
                                    "UTF-8"
                                ) ?>"
                            >
                        </div>

                        <div class="mb-3">
                            <label for="telefono" class="form-label">
                                Teléfono
                            </label>

                            <input
                                type="text"
                                id="telefono"
                                name="telefono"
                                class="form-control"
                                maxlength="30"
                                value="<?= htmlspecialchars(
                                    $_POST["telefono"] ?? "",
                                    ENT_QUOTES,
                                    "UTF-8"
                                ) ?>"
                            >
                        </div>

                        <div class="alert alert-info">
                            <strong>PIN de acceso:</strong>
                            se generará automáticamente al guardar el empleado.
                            Será numérico y tendrá 4 dígitos.
                        </div>

                        <button type="submit" class="btn btn-primary">
                            Guardar empleado
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
