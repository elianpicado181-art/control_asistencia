
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Control de Asistencia</title>

    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
        rel="stylesheet"
    >
    <link rel="stylesheet" href="css/inicio.css">

</head>

<body>

    <header class="topbar">
        <div class="container">
            <h1>Control de Asistencia</h1>
        </div>
    </header>

    <main>
        <div class="welcome">
            <h2>Bienvenido al sistema</h2>

            <p>
                Selecciona la opción correspondiente para continuar.
            </p>

            <div class="row g-4">

                <div class="col-12 col-md-6">
                    <section class="access-card">
                        <div class="access-icon">🔐</div>

                        <h3>Administrador</h3>

                        <p>
                            Accede al panel administrativo para gestionar
                            empleados y consultar la información del sistema.
                        </p>

                        <a
                            href="login.php"
                            class="access-btn admin-btn"
                        >
                            Iniciar sesión
                        </a>
                    </section>
                </div>

                <div class="col-12 col-md-6">
                    <section class="access-card">
                        <div class="access-icon">🕒</div>

                        <h3>Empleado</h3>

                        <p>
                            Registra tu entrada o salida utilizando tu
                            número de cédula y tu código de acceso.
                        </p>

                        <a
                            href="marcacion.php"
                            class="access-btn employee-btn"
                        >
                            Marcar asistencia
                        </a>
                    </section>
                </div>

            </div>
        </div>
    </main>

    <footer>
        Sistema de gestión de asistencia
    </footer>

</body>
</html>
