
<?php
require_once "../config/auth.php";
require_once "../config/database.php";

// Obtener el texto de búsqueda
$busqueda = trim($_GET["buscar"] ?? "");

// Consultar empleados
$sql = "SELECT * FROM empleados";
$params = [];

if ($busqueda !== "") {
    $sql .= " WHERE cedula LIKE :cedula
              OR nombre LIKE :nombre";

    $params[":cedula"] = "%{$busqueda}%";
    $params[":nombre"] = "%{$busqueda}%";
}

$sql .= " ORDER BY id DESC";

$stmt = $conexion->prepare($sql);
$stmt->execute($params);
$empleados = $stmt->fetchAll();

// Función para proteger la salida HTML
function escapar($valor)
{
    return htmlspecialchars(
        (string)($valor ?? ""),
        ENT_QUOTES,
        "UTF-8"
    );
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>Administración de empleados</title>

    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
        rel="stylesheet"
    >
</head>

<body class="bg-light">

<!-- Barra de navegación -->
<nav class="navbar navbar-dark bg-primary shadow-sm">
    <div class="container">
        <a class="navbar-brand fw-semibold" href="index.php">
            Control de asistencia
        </a>

        <a href="../logout.php"
           class="btn btn-outline-light btn-sm">
            Cerrar sesión
        </a>
    </div>
</nav>

<!-- Contenido principal -->
<div class="container py-4">

    <!-- Encabezado -->
    <div class="d-flex flex-wrap justify-content-between
                align-items-center gap-3 mb-4">

        <div>
            <h2 class="mb-1">Empleados</h2>
            <p class="text-muted mb-0">
                Administra la información de los empleados.
            </p>
        </div>

        <a href="registrar_empleado.php"
           class="btn btn-primary">
            + Nuevo empleado
        </a>
    </div>

    <!-- Mensajes -->
    <?php if (isset($_GET["registrado"])): ?>
        <div class="alert alert-success alert-dismissible fade show">
            Empleado registrado correctamente.
            <button type="button" class="btn-close"
                    data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <?php if (isset($_GET["actualizado"])): ?>
        <div class="alert alert-success alert-dismissible fade show">
            Empleado actualizado correctamente.
            <button type="button" class="btn-close"
                    data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <!-- Buscador -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body">
            <form method="GET" class="row g-2">

                <div class="col-md-8">
                    <label for="buscar" class="form-label">
                        Buscar empleado
                    </label>

                    <input
                        type="text"
                        name="buscar"
                        id="buscar"
                        class="form-control"
                        placeholder="Ingrese la cédula o el nombre"
                        value="<?= escapar($busqueda) ?>"
                    >
                </div>

                <div class="col-md-4 d-flex align-items-end gap-2">
                    <button type="submit"
                            class="btn btn-dark">
                        Buscar
                    </button>

                    <a href="empleados.php"
                       class="btn btn-outline-secondary">
                        Limpiar
                    </a>
                </div>

            </form>
        </div>
    </div>

    <!-- Tabla de empleados -->
    <div class="card border-0 shadow-sm">

        <div class="card-header bg-white py-3">
            <h5 class="mb-0">Listado de empleados</h5>
        </div>

        <div class="table-responsive">

            <table class="table table-hover align-middle mb-0">

                <thead class="table-dark">
                    <tr>
                        <th>Cédula</th>
                        <th>Nombre</th>
                        <th>Correo</th>
                        <th>Teléfono</th>
                        <th>Código de acceso</th>
                        <th>Estado</th>
                        <th>Fecha de registro</th>
                        <th>Acciones</th>
                    </tr>
                </thead>

                <tbody>

                <?php if (empty($empleados)): ?>

                    <tr>
                        <td colspan="8"
                            class="text-center py-5">

                            <h5 class="text-muted">
                                No se encontraron empleados
                            </h5>

                            <p class="text-muted mb-0">
                                Registra un empleado nuevo o cambia
                                los criterios de búsqueda.
                            </p>

                        </td>
                    </tr>

                <?php else: ?>

                    <?php foreach ($empleados as $empleado): ?>

                        <tr>

                            <td>
                                <?= escapar($empleado["cedula"]) ?>
                            </td>

                            <td class="fw-semibold">
                                <?= escapar($empleado["nombre"]) ?>
                            </td>

                            <td>
                                <?= escapar($empleado["correo"]) ?: "—" ?>
                            </td>

                            <td>
                                <?= escapar($empleado["telefono"]) ?: "—" ?>
                            </td>

                            <td>
                                <code class="fw-bold">
                                    <?= escapar($empleado["codigo_acceso"]) ?>
                                </code>
                            </td>

                            <td>
                                <?php if ($empleado["estado"] === "Activo"): ?>

                                    <span class="badge text-bg-success">
                                        Activo
                                    </span>

                                <?php else: ?>

                                    <span class="badge text-bg-secondary">
                                        Inactivo
                                    </span>

                                <?php endif; ?>
                            </td>

                            <td>
                                <?= escapar($empleado["fecha_registro"]) ?>
                            </td>

<td>
    <a href="editar_empleado.php?id=<?= (int)$empleado['id'] ?>"
       class="btn btn-warning btn-sm">
        Editar
    </a>

    <form action="eliminar_empleado.php"
          method="POST"
          class="d-inline"
          onsubmit="return confirm('¿Seguro que deseas eliminar este empleado?');">

        <input type="hidden"
               name="id"
               value="<?= (int)$empleado['id'] ?>">

        <button type="submit" class="btn btn-danger btn-sm">
            Eliminar
        </button>
    </form>
</td>


                        </tr>

                    <?php endforeach; ?>

                <?php endif; ?>

                </tbody>
            </table>

        </div>

        <div class="card-footer bg-white text-muted">
            Total de resultados: <?= count($empleados) ?>
        </div>

    </div>

    <!-- Navegación -->
    <div class="mt-4">
        <a href="index.php" class="btn btn-outline-primary">
            &larr; Volver al dashboard
        </a>
    </div>

</div>

<script
    src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js">
</script>

</body>
</html>
