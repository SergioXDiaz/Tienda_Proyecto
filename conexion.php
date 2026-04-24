<?php
// conexion.php

// 1. Credenciales de la base de datos
$servidor   = "localhost";
$usuario    = "root";
$password   = "";
$base_datos = "tienda_proyecto";

// 2. Crear la conexión usando la extensión mysqli
$conn = mysqli_connect($servidor, $usuario, $password, $base_datos);

// 3. Verificar si hubo errores
if (!$conn) {
    die("Error crítico: No se pudo conectar a la base de datos. " . mysqli_connect_error());
}

// 4. Establecer el conjunto de caracteres a UTF-8
// Vital para que los nombres de productos, categorías y comentarios acepten tildes y "ñ"
if (!mysqli_set_charset($conn, "utf8")) {
    die("Error cargando el conjunto de caracteres utf8: " . mysqli_error($conn));
}
// Aquí termina el archivo. No pongas nada más debajo de esta línea.