<?php
session_start();
require 'conexion.php';
$mensaje = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nombre = mysqli_real_escape_string($conn, $_POST['nombre']);
    $email  = mysqli_real_escape_string($conn, $_POST['email']);
    $pass   = $_POST['pass'];

    $checkEmail = mysqli_query($conn, "SELECT id_usuario FROM usuarios WHERE email = '$email'");
    
    if (mysqli_num_rows($checkEmail) > 0) {
        $mensaje = '<div class="alert alert-danger py-2 small"><i class="bi bi-exclamation-triangle-fill me-2"></i>Este email ya existe.</div>';
    } else {
        $sql_registro = "INSERT INTO usuarios (nombre, email, password, rol) 
                         VALUES('$nombre', '$email', MD5('$pass'), 'cliente')";

        if (mysqli_query($conn, $sql_registro)) {
            $mensaje = '<div class="alert alert-success py-2 small"><i class="bi bi-check-circle-fill me-2"></i>¡Éxito! <a href="login.php" class="alert-link text-dark">Inicia sesión aquí</a></div>';
        } else {
            $mensaje = '<div class="alert alert-danger py-2 small">Error: ' . mysqli_error($conn) . '</div>';
        }
    }
}

// Incluimos el header que ya tiene los estilos corregidos
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
// Incluimos el footer corregido
include 'footer.php'; 
?>