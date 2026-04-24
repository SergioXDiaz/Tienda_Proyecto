<?php
session_start();
require 'conexion.php';
$mensaje = "";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $pass  = $_POST['pass']; 

    $sql = "SELECT * FROM usuarios WHERE email = '$email'";
    $res = mysqli_query($conn, $sql);

    if ($fila = mysqli_fetch_assoc($res)) {
        if (md5($pass) == $fila['password']) { 

            // Guardamos todo en la sesión
            $_SESSION['id_usuario'] = $fila['id_usuario'];
            $_SESSION['nombre'] = $fila['nombre'];
            $_SESSION['rol'] = $fila['rol']; 

            // --- LÓGICA DE REDIRECCIÓN SEGÚN ROL ---
            if ($fila['rol'] == 'admin') {
                // El administrador va a su panel global
                header("Location: panel_admin.php");
            } else if ($fila['rol'] == 'gestor') {
                // El gestor va directamente a pedidos
                header("Location: gestion_pedidos.php");
            } else {
                // Clientes a la tienda
                header("Location: index.php");
            }
            exit;
            
        } else {
            $mensaje = "<div class='alert alert-danger border-0 small py-2 text-center'>Contraseña incorrecta</div>";
        }
    } else {
        $mensaje = "<div class='alert alert-danger border-0 small py-2 text-center'>El email no está registrado</div>";
    }
}

include 'header.php'; 
?>

<div class="container d-flex align-items-center justify-content-center flex-grow-1" style="min-height: 80vh;">
    <div class="row justify-content-center w-100">
        <div class="col-md-5 col-lg-4 text-center">
            
            <a href="index.php">
                <img src="img/logo.png" alt="Logo" style="height: 100px; margin-bottom: 20px; border-radius: 50%;">
            </a>

            <div class="card card-luxury">
                <div class="card-body p-5 text-start">
                    <h3 class="text-center fw-bold mb-4 text-gold">Bienvenido</h3>
                    
                    <?= $mensaje ?>
                    
                    <form action="login.php" method="POST">
                        <div class="mb-3">
                            <label class="form-label small fw-bold text-gold">Email</label>
                            <div class="input-group">
                                <span class="input-group-text bg-dark border-secondary text-gold"><i class="bi bi-envelope"></i></span>
                                <input type="email" class="form-control bg-dark text-white border-secondary shadow-none" name="email" required autofocus>
                            </div>
                        </div>
                        
                        <div class="mb-4">
                            <label class="form-label small fw-bold text-gold">Contraseña</label>
                            <div class="input-group">
                                <span class="input-group-text bg-dark border-secondary text-gold"><i class="bi bi-lock"></i></span>
                                <input type="password" class="form-control bg-dark text-white border-secondary shadow-none" name="pass" required>
                            </div>
                        </div>

                        <div class="d-grid mb-3">
                            <button type="submit" class="btn btn-gold">Entrar a mi cuenta</button>
                        </div>

                        <div class="text-center mt-4">
                            <span class="small text-muted-gold">¿No tienes cuenta?</span> <br>
                            <a href="registro.php" class="small fw-bold text-decoration-none text-gold">Regístrate gratis aquí</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'footer.php'; ?>