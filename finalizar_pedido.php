<?php
session_start();
require 'conexion.php';

// Verificación de carrito y usuario
if (empty($_SESSION['carrito']) || !isset($_SESSION['id_usuario'])) {
    header("Location: login.php");
    exit;
}

$id_user = $_SESSION['id_usuario'];
$total = 0;

// 1. Calcular el total acumulado del carrito (Corregido para array asociativo)
foreach ($_SESSION['carrito'] as $id => $cantidad) {
    $id = intval($id);
    $res = mysqli_query($conn, "SELECT precio FROM productos WHERE id_producto = $id");
    $p = mysqli_fetch_assoc($res);
    if($p) {
        $total += ($p['precio'] * $cantidad);
    }
}

// 2. Insertar el registro principal en la tabla 'pedidos'
$sql_pedido = "INSERT INTO pedidos (id_usuario, estado, total) VALUES ('$id_user', 'pendiente', '$total')";

if (mysqli_query($conn, $sql_pedido)) {
    $id_pedido_generado = mysqli_insert_id($conn); 

    // 3. Insertar el desglose en 'pedido_detalle'
    foreach ($_SESSION['carrito'] as $id => $cantidad) {
        $id = intval($id);
        $cantidad = intval($cantidad);
        
        $res_p = mysqli_query($conn, "SELECT precio FROM productos WHERE id_producto = $id");
        $prod_info = mysqli_fetch_assoc($res_p);
        
        if($prod_info) {
            $precio_u = $prod_info['precio'];
            
            // Insertamos el ID correcto y la cantidad real que eligió el cliente
            $sql_detalle = "INSERT INTO pedido_detalle (id_pedido, id_producto, cantidad, precio_unitario) 
                            VALUES ('$id_pedido_generado', '$id', '$cantidad', '$precio_u')";
            
            mysqli_query($conn, $sql_detalle);
            
            // Restar la cantidad comprada al stock del producto
            mysqli_query($conn, "UPDATE productos SET stock = stock - $cantidad WHERE id_producto = $id");
        }
    }

    // 4. Limpiar el carrito de la sesión
    unset($_SESSION['carrito']);

    // 5. Redirección a la página de éxito
    header("Location: pedido_exito.php?id=" . $id_pedido_generado);
    exit;

} else {
    die("Error crítico al procesar el pedido: " . mysqli_error($conn));
}
?>