
<?php
require_once "../config/auth.php";
require_once "../config/database.php";

date_default_timezone_set("America/Costa_Rica");

function escapar($valor) {
    return htmlspecialchars((string)($valor ?? ""), ENT_QUOTES, "UTF-8");
}

$busqueda = trim($_GET["busqueda"] ?? "");
$fechaDesde = trim($_GET["desde"] ?? "");
$fechaHasta = trim($_GET["hasta"] ?? "");

if ($fechaDesde !== "" &&
    !preg_match('/^\d{4}-\d{2}-\d{2}$/', $fechaDesde)) {
    $fechaDesde = "";
}

if ($fechaHasta !== "" &&
    !preg_match('/^\d{4}-\d{2}-\d{2}$/', $fechaHasta)) {
    $fechaHasta = "";
}

$pagina = max(1, (int)($_GET["pagina"] ?? 1));
$porPagina = 20;
$offset = ($pagina - 1) * $porPagina;

$where = [];
$params = [];

if ($busqueda !== "") {
    $where[] = "(e.nombre LIKE :nombre OR e.cedula LIKE :cedula)";
    $params[":nombre"] = "%".$busqueda."%";
    $params[":cedula"] = "%".$busqueda."%";
}

if ($fechaDesde !== "") {
    $where[] = "a.fecha >= :desde";
    $params[":desde"] = $fechaDesde;
}

if ($fechaHasta !== "") {
    $where[] = "a.fecha <= :hasta";
    $params[":hasta"] = $fechaHasta;
}

$whereSql = $where ? "WHERE ".implode(" AND ", $where) : "";

try {
    $sqlTotal = "SELECT COUNT(*)
                 FROM asistencias a
                 INNER JOIN empleados e ON e.id = a.empleado_id
                 $whereSql";

    $stmtTotal = $conexion->prepare($sqlTotal);
    $stmtTotal->execute($params);
    $totalRegistros = (int)$stmtTotal->fetchColumn();

    $totalPaginas = max(1, (int)ceil($totalRegistros / $porPagina));

    if ($pagina > $totalPaginas) {
        $pagina = $totalPaginas;
        $offset = ($pagina - 1) * $porPagina;
    }

    $sql = "SELECT
                a.id,
                e.cedula,
                e.nombre,
                a.fecha,
                a.hora_entrada,
                a.hora_salida,
                CASE
                    WHEN a.hora_entrada IS NOT NULL
                     AND a.hora_salida IS NOT NULL
                    THEN TIMESTAMPDIFF(
                        MINUTE,
                        CONCAT(a.fecha, ' ', a.hora_entrada),
                        CONCAT(a.fecha, ' ', a.hora_salida)
                    )
                    ELSE NULL
                END AS minutos_trabajados
            FROM asistencias a
            INNER JOIN empleados e ON e.id = a.empleado_id
            $whereSql
            ORDER BY a.fecha DESC, a.hora_entrada DESC
            LIMIT $porPagina OFFSET $offset";

    $stmt = $conexion->prepare($sql);
    $stmt->execute($params);
    $asistencias = $stmt->fetchAll();

} catch (PDOException $e) {
    error_log($e->getMessage());
    exit("No fue posible consultar el historial. Revisa la conexión y la estructura de la base de datos.");
}

$nombreAdmin = $_SESSION["admin_nombre"] ?? "Administrador";
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>Historial de asistencias</title>

    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
        rel="stylesheet"
    >
    <link rel="stylesheet" href="../css/asistencias.css">
</head>

<body>

<nav class="navbar navbar-dark">
    <div class="container">
        <a href="index.php" class="navbar-brand fw-semibold">
            Control de asistencia
        </a>

        <div class="d-flex align-items-center gap-3">
            <span class="text-white d-none d-md-inline">
                <?= escapar($nombreAdmin) ?>
            </span>
            <a href="../logout.php" class="btn btn-outline-light btn-sm">
                Cerrar sesión
            </a>
        </div>
    </div>
</nav>

<main class="container py-4">

    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
        <div>
            <h1 class="h3 fw-bold mb-1">Historial de asistencias</h1>
            <p class="text-secondary mb-0">
                Consulta las entradas, salidas y horas registradas.
            </p>
        </div>

        <a href="index.php" class="btn btn-secondary">
            Volver al panel
        </a>
    </div>

    <section class="panel p-3 p-md-4 mb-4">
        <form method="GET" action="asistencias.php">
            <div class="filters">

                <div>
                    <label for="busqueda" class="form-label">
                        Empleado o cédula
                    </label>
                    <input
                        type="text"
                        id="busqueda"
                        name="busqueda"
                        class="form-control"
                        placeholder="Buscar empleado"
                        value="<?= escapar($busqueda) ?>"
                    >
                </div>

                <div>
                    <label for="desde" class="form-label">Fecha desde</label>
                    <input
                        type="date"
                        id="desde"
                        name="desde"
                        class="form-control"
                        value="<?= escapar($fechaDesde) ?>"
                    >
                </div>

                <div>
                    <label for="hasta" class="form-label">Fecha hasta</label>
                    <input
                        type="date"
                        id="hasta"
                        name="hasta"
                        class="form-control"
                        value="<?= escapar($fechaHasta) ?>"
                    >
                </div>

                <div class="d-flex align-items-end gap-2">
                    <button type="submit" class="btn btn-primary flex-grow-1">
                        Buscar
                    </button>
                    <a href="asistencias.php" class="btn btn-outline-secondary">
                        Limpiar
                    </a>
                </div>

            </div>
        </form>
    </section>

    <section class="panel p-3 p-md-4">

        <div class="d-flex flex-wrap justify-content-between gap-2 mb-3">
            <h2 class="h5 fw-bold mb-0">Registros encontrados</h2>
            <span class="text-secondary">
                Total: <?= $totalRegistros ?>
            </span>
        </div>

        <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead class="table-light">
                    <tr>
                        <th>Fecha</th>
                        <th>Cédula</th>
                        <th>Empleado</th>
                        <th>Entrada</th>
                        <th>Salida</th>
                        <th>Horas trabajadas</th>
                        <th>Estado</th>
                    </tr>
                </thead>

                <tbody>
                <?php if (!$asistencias): ?>
                    <tr>
                        <td colspan="7" class="text-center text-secondary py-4">
                            No se encontraron registros de asistencia.
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($asistencias as $a): ?>
                        <?php
                        $minutos = $a["minutos_trabajados"];
                        $horasTexto = "—";

                        if ($minutos !== null && (int)$minutos >= 0) {
                            $minutos = (int)$minutos;
                            $horasTexto = intdiv($minutos, 60)
                                ." h ".($minutos % 60)." min";
                        }

                        if (empty($a["hora_entrada"])) {
                            $estado = "Sin entrada";
                            $clase = "secondary";
                        } elseif (empty($a["hora_salida"])) {
                            $estado = "En jornada";
                            $clase = "warning";
                        } else {
                            $estado = "Completa";
                            $clase = "success";
                        }
                        ?>
                        <tr>
                            <td><?= escapar(date("d/m/Y", strtotime($a["fecha"]))) ?></td>
                            <td><?= escapar($a["cedula"]) ?></td>
                            <td><?= escapar($a["nombre"]) ?></td>
                            <td><?= escapar($a["hora_entrada"] ?: "—") ?></td>
                            <td><?= escapar($a["hora_salida"] ?: "—") ?></td>
                            <td><?= escapar($horasTexto) ?></td>
                            <td>
                                <span class="badge text-bg-<?= $clase ?>">
                                    <?= escapar($estado) ?>
                                </span>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
                </tbody>
            </table>
        </div>

        <?php if ($totalRegistros > 0 && $totalPaginas > 1): ?>
            <nav aria-label="Paginación del historial">
                <ul class="pagination justify-content-center flex-wrap mb-0">

                    <?php
                    $queryBase = [
                        "busqueda" => $busqueda,
                        "desde" => $fechaDesde,
                        "hasta" => $fechaHasta
                    ];
                    ?>

                    <li class="page-item <?= $pagina <= 1 ? "disabled" : "" ?>">
                        <a
                            class="page-link"
                            href="?<?= escapar(http_build_query($queryBase + ["pagina" => max(1, $pagina - 1)])) ?>"
                        >Anterior</a>
                    </li>

                    <li class="page-item disabled">
                        <span class="page-link">
                            Página <?= $pagina ?> de <?= $totalPaginas ?>
                        </span>
                    </li>

                    <li class="page-item <?= $pagina >= $totalPaginas ? "disabled" : "" ?>">
                        <a
                            class="page-link"
                            href="?<?= escapar(http_build_query($queryBase + ["pagina" => min($totalPaginas, $pagina + 1)])) ?>"
                        >Siguiente</a>
                    </li>

                </ul>
            </nav>
        <?php endif; ?>

    </section>

</main>

</body>
</html>
