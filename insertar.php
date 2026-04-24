<?php
// 1. Incluimos la conexión
require 'conexion.php';

$mensaje = ""; 

// 2. Comprobamos si el formulario ha sido enviado
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    // Recogemos los datos del formulario de viajes
    $nombre      = $_POST['nombre'];
    $descripcion = $_POST['descripcion'];
    $precio      = $_POST['precio'];
    $clima       = $_POST['clima'];
    $tipo        = $_POST['tipo'];

    // 3. Preparamos la consulta SQL para la tabla DESTINOS
    $sql = "INSERT INTO destinos (nombre, descripcion, precio, clima, tipo) 
            VALUES ('$nombre', '$descripcion', $precio, '$clima', '$tipo')";

    // 4. Ejecutamos la consulta
    if (mysqli_query($conn, $sql)) {
        $mensaje = '<div class="alert alert-success">¡Nuevo destino añadido con éxito!</div>';
    } else {
        $mensaje = '<div class="alert alert-danger">Error al guardar: ' . mysqli_error($conn) . '</div>';
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Añadir Destino - Agencia de Viajes</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">

    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-6">
                
                <div class="card shadow border-0">
                    <div class="card-header bg-success text-white text-center">
                        <h3>Añadir Nuevo Destino</h3>
                    </div>
                    <div class="card-body p-4">
                        
                        <?php echo $mensaje; ?>

                        <form action="insertar.php" method="POST">
                            <div class="mb-3">
                                <label class="form-label">Nombre del Destino</label>
                                <input type="text" name="nombre" class="form-control" placeholder="Ej: Islas Maldivas" required>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Descripción</label>
                                <textarea name="descripcion" class="form-control" rows="3" required></textarea>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Precio (€)</label>
                                <input type="number" step="0.01" name="precio" class="form-control" placeholder="Ej: 1200.50" required>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Clima</label>
                                <select name="clima" class="form-select" required>
                                    <option value="calido">Cálido</option>
                                    <option value="frio">Frío</option>
                                    <option value="templado">Templado</option>
                                </select>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Tipo de Viaje</label>
                                <select name="tipo" class="form-select" required>
                                    <option value="relax">Relax</option>
                                    <option value="aventura">Aventura</option>
                                    <option value="cultura">Cultura</option>
                                </select>
                            </div>

                            <div class="d-grid gap-2">
                                <button type="submit" class="btn btn-success">Guardar Destino</button>
                                <a href="mostrar.php" class="btn btn-outline-secondary">Ver Catálogo</a>
                            </div>
                        </form>

                    </div>
                </div>

            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>