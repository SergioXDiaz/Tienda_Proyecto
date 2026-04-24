<?php
session_start();
if (isset($_SESSION['rol']) && $_SESSION['rol'] == 'admin') { 
    require 'conexion.php';
    $mensaje = "";

    // --- LÓGICA DE ACTUALIZACIÓN ---
    if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['btn_actualizar'])) {
        $id          = $_POST['id'];
        $nombre      = mysqli_real_escape_string($conn, $_POST['nombre']);
        $descripcion = mysqli_real_escape_string($conn, $_POST['descripcion']);
        $precio      = $_POST['precio'];

        $sql_update = "UPDATE destinos SET nombre='$nombre', descripcion='$descripcion', precio=$precio WHERE id=$id";
        if (mysqli_query($conn, $sql_update)) {
            $mensaje = '<div class="alert alert-success alert-dismissible fade show shadow-sm border-0 mb-4">
                            <i class="bi bi-check-circle-fill me-2"></i> ¡Destino actualizado con éxito!
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>';
        }
    } 
    
    // --- LÓGICA DE ELIMINACIÓN ---
    elseif ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['btn_eliminar'])) {
        $id = $_POST['id'];
        $sql_delete = "DELETE FROM destinos WHERE id=$id";
        if (mysqli_query($conn, $sql_delete)) {
            $mensaje = '<div class="alert alert-danger alert-dismissible fade show shadow-sm border-0 mb-4">
                            <i class="bi bi-trash-fill me-2"></i> Destino eliminado correctamente.
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>';
        }
    }

    $sql = "SELECT * FROM destinos";
    $resultado = mysqli_query($conn, $sql);
} else {
    die("Acceso denegado.");
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Panel de Control - Viajes Astigi</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <style>
        body {
            background-color: #ffffff;
            font-family: 'Segoe UI', system-ui, -apple-system, sans-serif;
        }

        /* Navbar estilo corporativo */
        .admin-nav {
            background-color: #212529;
            padding: 12px 0;
            color: white;
        }

        .logo-nav {
            height: 40px;
            width: auto;
            border-radius: 4px;
        }

        /* Sección Hero Azul */
        .hero-admin {
            background-color: #007bff;
            color: white;
            padding: 50px 0;
            text-align: center;
            margin-bottom: 40px;
        }

        /* Tabla Estilizada */
        .table thead th {
            background-color: #f8f9fa;
            color: #007bff;
            text-transform: uppercase;
            font-size: 0.8rem;
            letter-spacing: 1px;
            padding: 15px;
            border-bottom: 2px solid #007bff;
        }

        .table-input {
            border: 1px solid #dee2e6;
            border-radius: 6px;
            padding: 8px;
            width: 100%;
        }

        .table-input:focus {
            border-color: #007bff;
            outline: none;
            box-shadow: 0 0 0 3px rgba(0,123,255,0.1);
        }

        /* Botones */
        .btn-save {
            background-color: #28a745;
            color: white;
            font-weight: 500;
        }

        .btn-save:hover { background-color: #218838; color: white; }

        .btn-exit {
            background-color: #dc3545;
            color: white;
            border-radius: 6px;
            padding: 6px 15px;
            text-decoration: none;
        }
    </style>
</head>
<body>

    <nav class="admin-nav shadow-sm">
        <div class="container d-flex justify-content-between align-items-center">
            <div class="d-flex align-items-center">
                <img src="img/logo.jpg" alt="Logo Viajes Astigi" class="logo-nav me-3">
                <strong class="h5 mb-0">Viajes Astigi <span class="badge bg-primary ms-2">Admin</span></strong>
            </div>
            <div class="d-flex align-items-center">
                <span class="me-3 d-none d-md-inline text-white-50">Sesión de: <strong><?php echo $_SESSION['nombre']; ?></strong></span>
                <a href="logout.php" class="btn-exit btn-sm">Salir</a>
            </div>
        </div>
    </nav>

    <header class="hero-admin shadow-sm">
        <div class="container">
            <h1 class="display-5 fw-bold">Gestión de Destinos</h1>
            <p class="lead text-white-50">Panel de administración</p>
        </div>
    </header>

    <div class="container mb-5">
        <?php echo $mensaje; ?>

        <div class="table-responsive bg-white rounded shadow p-4">
            <table class="table align-middle">
                <thead>
                    <tr>
                        <th width="5%">ID</th>
                        <th width="20%">Nombre del Destino</th>
                        <th width="40%">Descripción</th>
                        <th width="15%">Precio</th>
                        <th width="20%" class="text-center">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while($fila = mysqli_fetch_assoc($resultado)): ?>
                    <tr>
                        <form action="modificar.php" method="POST">
                            <input type="hidden" name="id" value="<?php echo $fila['id']; ?>">
                            <td class="text-muted fw-bold">#<?php echo $fila['id']; ?></td>
                            <td>
                                <input type="text" name="nombre" class="table-input fw-bold text-primary" value="<?php echo $fila['nombre']; ?>">
                            </td>
                            <td>
                                <textarea name="descripcion" class="table-input" rows="2"><?php echo $fila['descripcion']; ?></textarea>
                            </td>
                            <td>
                                <div class="input-group">
                                    <span class="input-group-text bg-light border-end-0">€</span>
                                    <input type="number" step="0.01" name="precio" class="form-control border-start-0" value="<?php echo $fila['precio']; ?>">
                                </div>
                            </td>
                            <td class="text-center">
                                <div class="d-flex gap-2 justify-content-center">
                                    <button type="submit" name="btn_actualizar" class="btn btn-save btn-sm px-3">Actualizar</button>
                                    <button type="submit" name="btn_eliminar" class="btn btn-outline-danger btn-sm border-0" onclick="return confirm('¿Eliminar definitivamente?')">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </div>
                            </td>
                        </form>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>

        <div class="mt-5 d-flex justify-content-center gap-3">
            <a href="insertar.php" class="btn btn-primary btn-lg px-4 shadow-sm">
                <i class="bi bi-plus-circle me-2"></i>Nuevo Destino
            </a>
            <a href="mostrar.php" class="btn btn-outline-secondary btn-lg px-4">
                <i class="bi bi-eye me-2"></i>Ver Web Pública
            </a>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>