
<?php
session_start();

// Vaciar los datos de la sesión
$_SESSION = [];

// Destruir la sesión actual
session_destroy();

// Regresar a la página principal con las opciones de acceso
header("Location: index.php");
exit;
