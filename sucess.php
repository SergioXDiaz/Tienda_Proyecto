<?php
session_start();

// Vaciar carrito después del pago
unset($_SESSION['carrito']);
?>

<h2>✅ Pago realizado correctamente</h2>
<a href="catalogo.php">Volver a la tienda</a>