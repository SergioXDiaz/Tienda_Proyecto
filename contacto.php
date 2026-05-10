<?php
session_start();
require 'conexion.php';

$id_user = isset($_SESSION['id_usuario']) ? $_SESSION['id_usuario'] : null;

// Lógica para enviar mensaje
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['nuevo_mensaje']) && $id_user) {
    $msg = mysqli_real_escape_string($conn, $_POST['mensaje']);
    mysqli_query($conn, "INSERT INTO mensajes_contacto (id_usuario, mensaje) VALUES ('$id_user', '$msg')");
    $exito = "Mensaje enviado correctamente. Te responderemos lo antes posible.";
}

$res = null;
if ($id_user) {
    $res = mysqli_query($conn, "SELECT * FROM mensajes_contacto WHERE id_usuario = '$id_user' ORDER BY fecha_envio DESC");
}

include 'header.php';
include 'navbar.php';
?>

<div class="container mt-5" style="min-height: 70vh;">
    <?php if($id_user): ?>
        <h2 class="text-gold fw-bold mb-4">Contacto con nosotros</h2>
        
        <div class="row">
            <div class="col-md-5 mb-4">
                <div class="card p-4 shadow" style="background: #1a1a1a; border: 1px solid #D4AF37;">
                    <h5 class="text-white mb-3">Enviar nueva consulta</h5>
                    <form method="POST">
                        <textarea name="mensaje" class="form-control mb-3" rows="4" placeholder="Escribe aquí tu duda..." required style="background: #333; color: white; border: 1px solid #444;"></textarea>
                        <button type="submit" name="nuevo_mensaje" class="btn btn-gold w-100 fw-bold">ENVIAR MENSAJE</button>
                    </form>
                    <?php if(isset($exito)) echo "<p class='text-success mt-2 small fw-bold'>$exito</p>"; ?>
                </div>
            </div>

            <div class="col-md-7">
                <h5 class="text-white mb-3">Mis consultas anteriores</h5>
                <?php if(mysqli_num_rows($res) > 0): ?>
                    <?php while($m = mysqli_fetch_assoc($res)): ?>
                        <div class="mb-3 p-3 rounded" style="background: #252525; border-left: 4px solid #D4AF37;">
                            <div class="d-flex justify-content-between">
                                <p class="text-gold mb-2 small"><strong>Tú</strong></p>
                                <p class="text-light opacity-75 small mb-1"><?= date('d/m/Y H:i', strtotime($m['fecha_envio'])) ?></p>
                            </div>
                            <p class="text-white mb-2"><?= htmlspecialchars($m['mensaje']) ?></p>
                            
                            <?php if($m['respuesta_gestor']): ?>
                                <div class="mt-2 p-2 rounded" style="background: #111; border: 1px solid #D4AF37;">
                                    <p class="text-gold mb-1 small"><strong>Respuesta de Angelina Shop:</strong></p>
                                    <p class="text-white mb-0 small" style="font-style: italic;"><?= htmlspecialchars($m['respuesta_gestor']) ?></p>
                                </div>
                            <?php else: ?>
                                <p class="text-gold small mt-2 opacity-50" style="font-style: italic;">Esperando respuesta...</p>
                            <?php endif; ?>
                        </div>
                    <?php endwhile; ?>
                <?php else: ?>
                    <p class="text-white opacity-75">Aún no has realizado ninguna consulta.</p>
                <?php endif; ?>
            </div>
        </div>

    <?php else: ?>
        <div class="d-flex justify-content-center align-items-center" style="height: 60vh;">
            <div class="card p-5 text-center shadow-lg" style="background: #1a1a1a; border: 2px solid #D4AF37; max-width: 500px;">
                <i class="bi bi-person-lock text-gold mb-3" style="font-size: 3rem;"></i>
                <h4 class="text-white fw-bold mb-3">Acceso Restringido</h4>
                <p class="text-white opacity-75 mb-4">
                    Para enviarnos una consulta privada y revisar tu historial, 
                    debes iniciar sesión con tu cuenta de cliente.
                </p>
                <a href="login.php" class="btn btn-gold btn-lg w-100 fw-bold">INICIAR SESIÓN</a>
                <p class="mt-3 small text-white opacity-50">¿Aún no eres cliente? <a href="registro.php" class="text-gold">Crea tu cuenta aquí</a></p>
            </div>
        </div>
    <?php endif; ?>
</div>

<?php include 'footer.php'; ?>