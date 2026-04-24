<?php
session_start();
require 'conexion.php';
$mensaje = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $errores = [];

    // Limpiar datos
    $nombre = trim($_POST['nombre'] ?? '');
    $email  = trim($_POST['email'] ?? '');
    $pass   = $_POST['pass'] ?? '';

    // Validaciones
    if (empty($nombre)) {
        $errores[] = "El nombre es obligatorio";
    } elseif (strlen($nombre) < 3) {
        $errores[] = "El nombre debe tener al menos 3 caracteres";
    }

    if (empty($email)) {
        $errores[] = "El email es obligatorio";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errores[] = "El email no es válido";
    }

    if (empty($pass)) {
        $errores[] = "La contraseña es obligatoria";
    } elseif (strlen($pass) < 4) {
        $errores[] = "La contraseña debe tener mínimo 4 caracteres";
    }

    // Si hay errores
    if (!empty($errores)) {
        $mensaje = '<div class="alert alert-danger py-2 small">';
        foreach ($errores as $error) {
            $mensaje .= '<div><i class="bi bi-exclamation-triangle-fill me-2"></i>' . $error . '</div>';
        }
        $mensaje .= '</div>';
    } else {

        // Escapar datos
        $nombre = mysqli_real_escape_string($conn, $nombre);
        $email  = mysqli_real_escape_string($conn, $email);

        // Comprobar email existente
        $checkEmail = mysqli_query($conn, "SELECT id_usuario FROM usuarios WHERE email = '$email'");
        
        if (mysqli_num_rows($checkEmail) > 0) {
            $mensaje = '<div class="alert alert-danger py-2 small">
            <i class="bi bi-exclamation-triangle-fill me-2"></i>Este email ya existe.</div>';
        } else {

            // Hash seguro de contraseña
            $passwordHash = password_hash($pass, PASSWORD_DEFAULT);

            $sql_registro = "INSERT INTO usuarios (nombre, email, password, rol) 
                             VALUES('$nombre', '$email', '$passwordHash', 'cliente')";

            if (mysqli_query($conn, $sql_registro)) {
                $mensaje = '<div class="alert alert-success py-2 small">
                <i class="bi bi-check-circle-fill me-2"></i>¡Éxito! 
                <a href="login.php" class="alert-link text-dark">Inicia sesión aquí</a></div>';
            } else {
                $mensaje = '<div class="alert alert-danger py-2 small">
                Error: ' . mysqli_error($conn) . '</div>';
            }
        }
    }
}

// Incluimos el header
include 'header.php'; 
?>

<div class="container py-5 d-flex align-items-center justify-content-center flex-grow-1">
    <div class="row justify-content-center w-100">
        <div class="col-md-6 col-lg-4 text-center">
            
            <a href="index.php">
                <img src="img/logo.png" alt="Logo" style="height: 100px; margin-bottom: 20px;">
            </a>

            <div class="card card-luxury">
                <div class="card-body p-4 text-start">
                    <h3 class="text-center fw-bold mb-1 text-gold">Únete a nosotros</h3>
                    <p class="text-center text-muted-gold small mb-4">Crea tu cuenta en Angelina Shop</p>

                    <?= $mensaje ?>

                    <form action="registro.php" method="POST">
                        <div class="mb-3">
                            <label class="form-label small fw-bold text-gold">Nombre completo</label>
                            <div class="input-group">
                                <span class="input-group-text bg-dark border-secondary text-gold"><i class="bi bi-person"></i></span>
                                <input type="text" class="form-control bg-dark text-white border-secondary" name="nombre" placeholder="Tu nombre" required autofocus>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label small fw-bold text-gold">Email</label>
                            <div class="input-group">
                                <span class="input-group-text bg-dark border-secondary text-gold"><i class="bi bi-envelope"></i></span>
                                <input type="email" class="form-control bg-dark text-white border-secondary" name="email" placeholder="email@ejemplo.com" required>
                            </div>
                        </div>

                        <div class="mb-4">
                            <label class="form-label small fw-bold text-gold">Contraseña</label>
                            <div class="input-group">
                                <span class="input-group-text bg-dark border-secondary text-gold"><i class="bi bi-lock"></i></span>
                                <input type="password" class="form-control bg-dark text-white border-secondary" name="pass" placeholder="Mínimo 4 caracteres" required minlength="4">
                            </div>
                        </div>

                        <div class="d-grid">
                            <button type="submit" class="btn btn-gold">Crear mi cuenta</button>
                        </div>

                        <div class="mt-4 text-center">
                            <span class="text-muted-gold small">¿Ya eres cliente?</span> <br>
                            <a href="login.php" class="small fw-bold text-decoration-none text-gold">Inicia sesión ahora</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php 
include 'footer.php'; 
?>