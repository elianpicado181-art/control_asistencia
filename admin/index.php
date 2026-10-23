
<?php
require_once "../config/auth.php";
require_once "../config/database.php";

// Total de empleados
$stmt = $conexion->query("SELECT COUNT(*) FROM empleados");
$totalEmpleados = (int) $stmt->fetchColumn();

// Empleados activos
$stmt = $conexion->query(
    "SELECT COUNT(*) FROM empleados WHERE estado = 'Activo'"
);
$empleadosActivos = (int) $stmt->fetchColumn();

// Empleados que marcaron entrada hoy
$stmt = $conexion->query(
    "SELECT COUNT(DISTINCT empleado_id)
     FROM asistencias
     WHERE fecha = CURDATE() AND hora_entrada IS NOT NULL"
);
$asistieronHoy = (int) $stmt->fetchColumn();

// Empleados que siguen dentro
$stmt = $conexion->query(
    "SELECT COUNT(*)
     FROM asistencias
     WHERE fecha = CURDATE()
       AND hora_entrada IS NOT NULL
       AND hora_salida IS NULL"
);
$dentroAhora = (int) $stmt->fetchColumn();

$nombreAdmin = $_SESSION["admin_nombre"] ?? "Administrador";
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel administrativo | Control de asistencia</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
          rel="stylesheet">
    <link rel="stylesheet" href="../css/dashboard.css">
</head>

<body>

<nav class="navbar navbar-expand-lg">
    <div class="container">
        <a class="navbar-brand" href="index.php">
            Control de asistencia
        </a>

        <div class="d-flex align-items-center gap-3">
            <span class="text-white">
                <?= htmlspecialchars($nombreAdmin, ENT_QUOTES, "UTF-8") ?>
            </span>

            <a href="../logout.php" class="btn btn-outline-light btn-sm">
                Cerrar sesión
            </a>
        </div>
    </div>
</nav>

<main class="container pb-4">

    <header class="page-header">
        <h1 class="h3 fw-bold">Panel administrativo</h1>
        <p class="text-secondary mb-0">
            Bienvenido. Aquí puedes consultar y administrar el control de asistencia.
        </p>
    </header>

    <div class="row g-4 mb-4">

        <div class="col-12 col-sm-6 col-xl-3">
            <div class="stat-card">
                <div class="stat-label">Total de empleados</div>
                <div class="stat-number">
                    <?= $totalEmpleados ?>
                </div>
            </div>
        </div>

        <div class="col-12 col-sm-6 col-xl-3">
            <div class="stat-card">
                <div class="stat-label">Empleados activos</div>
                <div class="stat-number text-success">
                    <?= $empleadosActivos ?>
                </div>
            </div>
        </div>

        <div class="col-12 col-sm-6 col-xl-3">
            <div class="stat-card">
                <div class="stat-label">Empleados que marcaron hoy</div>
                <div class="stat-number text-primary">
                    <?= $asistieronHoy ?>
                </div>
            </div>
        </div>

        <div class="col-12 col-sm-6 col-xl-3">
            <div class="stat-card">
                <div class="stat-label">Dentro del establecimiento</div>
                <div class="stat-number text-warning">
                    <?= $dentroAhora ?>
                </div>
            </div>
        </div>

    </div>

    <div class="row g-4">

        <div class="col-12 col-lg-6">
            <section class="section-card">
                <h2 class="h5 fw-bold">Gestión de empleados</h2>
                <p class="text-secondary">
                    Administra los datos y el estado de los empleados.
                </p>

                <a href="empleados.php" class="menu-link">
                    <strong>Consultar empleados</strong>
                    <span>Ver, buscar y editar empleados registrados.</span>
                </a>

                <a href="registrar_empleado.php" class="menu-link">
                    <strong>Registrar empleado</strong>
                    <span>Agregar un empleado y generar su código de acceso.</span>
                </a>
            </section>
        </div>

        <div class="col-12 col-lg-6">
            <section class="section-card">
                <h2 class="h5 fw-bold">Control de asistencia</h2>
                <p class="text-secondary">
                    Accede a las funciones de marcación y consulta.
                </p>

                <a href="../marcacion.php" class="menu-link">
                    <strong>Página de marcación</strong>
                    <span>Abrir el formulario que utilizan los empleados.</span>
                </a>

                <a href="asistencias.php" class="menu-link">
                    <strong>Historial de asistencias</strong>
                    <span>Consultar entradas, salidas y registros de asistencia.</span>
                </a>
            </section>
        </div>

    </div>

</main>

<footer>
    Sistema de gestión de asistencia
</footer>

</body>
</html>
