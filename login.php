
<?php
session_start();
require_once "config/database.php";

$error = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $usuario = trim($_POST["usuario"] ?? "");
    $password = $_POST["password"] ?? "";

    if ($usuario !== "" && $password !== "") {
        $sql = "SELECT id, nombre, usuario, password
                FROM usuarios
                WHERE usuario = :usuario
                AND estado = 'Activo'
                LIMIT 1";

        $stmt = $conexion->prepare($sql);
        $stmt->execute([":usuario" => $usuario]);
        $admin = $stmt->fetch();

        if ($admin && password_verify($password, $admin["password"])) {
            session_regenerate_id(true);

            $_SESSION["admin_id"] = $admin["id"];
            $_SESSION["admin_nombre"] = $admin["nombre"];

            header("Location: admin/index.php");
            exit;
        }

        $error = "Usuario o contraseña incorrectos.";
    } else {
        $error = "Completa todos los campos.";
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Iniciar sesión</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
          rel="stylesheet">
</head>
<body class="bg-light">
<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-5 col-lg-4">
            <div class="card shadow-sm border-0">
                <div class="card-body p-4">
                    <h3 class="text-center mb-4">Control de asistencia</h3>

                    <?php if ($error !== ""): ?>
                        <div class="alert alert-danger">
                            <?= htmlspecialchars($error, ENT_QUOTES, "UTF-8") ?>
                        </div>
                    <?php endif; ?>

                    <form method="POST">
                        <div class="mb-3">
                            <label class="form-label">Usuario</label>
                            <input type="text" name="usuario"
                                   class="form-control" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Contraseña</label>
                            <input type="password" name="password"
                                   class="form-control" required>
                        </div>

                        <button type="submit" class="btn btn-primary w-100">
                            Iniciar sesión
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
</body>
</html>
