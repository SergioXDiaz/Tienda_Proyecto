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

// 1. Calcular el total acumulado del carrito (ahora con estructura de atributos)
foreach ($_SESSION['carrito'] as $item) {
    // Compatibilidad con formato antiguo (solo ID) y nuevo (array con atributos)
    if (is_numeric($item)) {
        $id_producto = $item;
        $cantidad = 1; // En formato antiguo, el valor era la cantidad
    } else {
        $id_producto = $item['id_producto'];
        $cantidad = $item['cantidad'];
    }
    
    $res = mysqli_query($conn, "SELECT precio FROM productos WHERE id_producto = $id_producto");
    $p = mysqli_fetch_assoc($res);
    if($p) {
        $total += ($p['precio'] * $cantidad);
    }
}

// 2. Insertar el registro principal en la tabla 'pedidos'
$sql_pedido = "INSERT INTO pedidos (id_usuario, estado, total) VALUES ('$id_user', 'pendiente', '$total')";

if (mysqli_query($conn, $sql_pedido)) {
    $id_pedido_generado = mysqli_insert_id($conn); 

    // 3. Insertar el desglose en 'pedido_detalle' CON ATRIBUTOS
    foreach ($_SESSION['carrito'] as $clave => $item) {
        
        // Compatibilidad con formato antiguo
        if (is_numeric($item)) {
            // Formato antiguo: $carrito[id_producto] = cantidad
            $id_producto = $clave;
            $cantidad = $item;
            $color = null;
            $talla = null;
            $personalizacion = null;
        } else {
            // Formato nuevo: array con atributos
            $id_producto = $item['id_producto'];
            $cantidad = $item['cantidad'];
            $color = $item['color'] ?? null;
            $talla = $item['talla'] ?? null;
            $personalizacion = $item['personalizacion'] ?? null;
        }
        
        $id_producto = intval($id_producto);
        $cantidad = intval($cantidad);
        
        // Escapar atributos para evitar inyección SQL
        $color_escape = $color ? "'" . mysqli_real_escape_string($conn, $color) . "'" : "NULL";
        $talla_escape = $talla ? "'" . mysqli_real_escape_string($conn, $talla) . "'" : "NULL";
        $personalizacion_escape = $personalizacion ? "'" . mysqli_real_escape_string($conn, $personalizacion) . "'" : "NULL";
        
        $res_p = mysqli_query($conn, "SELECT precio FROM productos WHERE id_producto = $id_producto");
        $prod_info = mysqli_fetch_assoc($res_p);
        
        if($prod_info) {
            $precio_u = $prod_info['precio'];
            
            // Insertamos los atributos en el detalle del pedido
            // NOTA: Necesitas que la tabla pedido_detalle tenga las columnas: color, talla, personalizacion
            $sql_detalle = "INSERT INTO pedido_detalle (id_pedido, id_producto, cantidad, precio_unitario, color, talla, personalizacion) 
                            VALUES ('$id_pedido_generado', '$id_producto', '$cantidad', '$precio_u', $color_escape, $talla_escape, $personalizacion_escape)";
            
            if (!mysqli_query($conn, $sql_detalle)) {
                // Si falla, mostramos el error pero continuamos (puede que falten columnas)
                error_log("Error en detalle: " . mysqli_error($conn));
            }
            
            // Restar la cantidad comprada al stock del producto
            mysqli_query($conn, "UPDATE productos SET stock = stock - $cantidad WHERE id_producto = $id_producto");
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