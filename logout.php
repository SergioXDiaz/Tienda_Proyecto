<?php
// 1. Unirse a la sesión actual para saber a quién estamos cerrando
session_start();

// 2. Borrar todas las variables de sesión (nombre, rol, id)
session_unset();

// 3. Destruir la sesión por completo en el servidor
session_destroy();

// 4. Redirigir al formulario de login (index.php)
header("Location: index.php");
exit;
?>