<?php
session_start();
require 'conexion.php';

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_SESSION['id_usuario'])) {
    $id_usuario = $_SESSION['id_usuario'];
    $id_destino = mysqli_real_escape_string($conn, $_POST['id_destino']);
    $tipo_tarifa = isset($_POST['tarifa']) ? mysqli_real_escape_string($conn, $_POST['tarifa']) : 'normal';
    
    // Capturamos la fecha del formulario
    // Si por algún motivo no llega, ponemos la de hoy como "failsafe", 
    // pero lo ideal es que siempre llegue la del POST.
    $fecha_viaje = isset($_POST['fecha_viaje']) ? mysqli_real_escape_string($conn, $_POST['fecha_viaje']) : date('Y-m-d');

    // Validación extra: Si la fecha está vacía, no dejamos insertar
    if (empty($fecha_viaje)) {
        header("Location: mostrar.php?error=fecha_vacia");
        exit;
    }

    // Insertamos la reserva con la fecha específica elegida por el usuario
    $sql = "INSERT INTO reservas (id_usuario, id_destino, fecha_viaje, tipo_tarifa) 
            VALUES ('$id_usuario', '$id_destino', '$fecha_viaje', '$tipo_tarifa')";

    if (mysqli_query($conn, $sql)) {
        // Redirigimos al listado de reservas del usuario
        header("Location: mis_reservas.php?reserva=exito");
        exit;
    } else {
        echo "Error en la reserva: " . mysqli_error($conn);
    }
} else {
    // Si intentan entrar al archivo sin enviar el formulario
    header("Location: mostrar.php");
    exit;
}
?>