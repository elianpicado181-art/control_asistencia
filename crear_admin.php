
<?php
require_once "config/database.php";

$nombre = "Administrador";
$usuario = "admin";
$password = password_hash("Admin123*", PASSWORD_DEFAULT);

try {
    $sql = "INSERT INTO usuarios (nombre, usuario, password)
            VALUES (:nombre, :usuario, :password)";

    $stmt = $conexion->prepare($sql);
    $stmt->execute([
        ":nombre" => $nombre,
        ":usuario" => $usuario,
        ":password" => $password
    ]);

    echo "Administrador creado correctamente.";

} catch (PDOException $e) {
    if ($e->getCode() == "23000") {
        echo "El usuario admin ya existe.";
    } else {
        error_log($e->getMessage());
        echo "No se pudo crear el administrador.";
    }
}
