<?php
session_start();
require 'conexion.php';

// SEGURIDAD: Solo Gestores y Admins
if (!isset($_SESSION['rol']) || ($_SESSION['rol'] != 'gestor' && $_SESSION['rol'] != 'admin')) {
    header("Location: index.php");
    exit;
}

// LÓGICA DE RESPUESTA
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['responder'])) {
    $id_msg = intval($_POST['id_mensaje']);
    $resp = mysqli_real_escape_string($conn, $_POST['respuesta']);
    mysqli_query($conn, "UPDATE mensajes_contacto SET respuesta_gestor = '$resp' WHERE id_mensaje = '$id_msg'");
    $aviso = "Respuesta enviada correctamente.";
}

// Listado de mensajes
$query = "SELECT m.*, u.nombre FROM mensajes_contacto m 
          JOIN usuarios u ON m.id_usuario = u.id_usuario 
          ORDER BY m.fecha_envio DESC";
$res_mensajes = mysqli_query($conn, $query);

include 'header.php'; 
?>

<style>
    body { background-color: #0b0b0b; color: white; }
    .text-gold { color: #D4AF37 !important; }
    .card-admin { background: #151515; border: 1px solid #333; border-radius: 15px; overflow: hidden; }
    .table-luxury thead { background: #000; color: #D4AF37; border-bottom: 2px solid #D4AF37; }
    .table-luxury tbody td { border-bottom: 1px solid #222; padding: 15px; color: white !important; }
    .btn-action { border: 1px solid #D4AF37; color: #D4AF37; background: transparent; transition: 0.3s; text-decoration: none; display: inline-block; }
    .btn-action:hover { background: #D4AF37; color: #000; }
    
    .sidebar { min-height: 100vh; background: #000; border-right: 1px solid #D4AF37; padding-top: 20px; }
    .nav-link { color: #aaa; padding: 12px 20px; transition: 0.3s; display: flex; align-items: center; text-decoration: none; }
    .nav-link:hover, .nav-link.active { color: #D4AF37; background: rgba(212, 175, 55, 0.1); border-left: 3px solid #D4AF37; }
    
    .modal-content { background: #151515; border: 1px solid #D4AF37; color: white; }
    .form-control { background: #000; border: 1px solid #444; color: white; }
    .form-control:focus { background: #050505; border-color: #D4AF37; color: white; box-shadow: none; }
</style>

<div class="container-fluid">
    <div class="row">
        
        <?php include 'menu_gestion.php'; ?>

        <div class="col-md-10 p-4 p-md-5">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2 class="fw-bold text-white mb-0">ATENCIÓN AL <span class="text-gold">CLIENTE</span></h2>
                <span class="badge border border-gold text-gold p-2">
                    <i class="bi bi-chat-left-dots me-1"></i> Bandeja de Entrada
                </span>
            </div>

            <?php if(isset($aviso)): ?>
                <div class="alert alert-dark border-gold text-gold alert-dismissible fade show" role="alert">
                    <i class="bi bi-check2-all me-2"></i> <?= $aviso ?>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>

            <div class="card-admin shadow-lg">
                <div class="table-responsive">
                    <table class="table table-dark table-hover align-middle mb-0 table-luxury">
                        <thead>
                            <tr>
                                <th class="ps-4">CLIENTE / FECHA</th>
                                <th>MENSAJE</th>
                                <th>ESTADO</th>
                                <th class="text-center">ACCIONES</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while($m = mysqli_fetch_assoc($res_mensajes)): ?>
                            <tr>
                                <td class="ps-4">
                                    <div class="fw-bold text-white"><?= htmlspecialchars($m['nombre']) ?></div>
                                    <small class="text-gold" style="font-size: 0.75rem;"><?= date('d/m/Y H:i', strtotime($m['fecha_envio'])) ?></small>
                                </td>
                                <td>
                                    <p class="small mb-0 text-white" style="line-height: 1.4;">
                                        <span class="text-gold fw-bold">"</span><?= htmlspecialchars($m['mensaje']) ?><span class="text-gold fw-bold">"</span>
                                    </p>
                                </td>
                                <td>
                                    <?php if($m['respuesta_gestor']): ?>
                                        <span class="badge bg-success px-3 rounded-pill">Respondido</span>
                                    <?php else: ?>
                                        <span class="badge bg-warning text-dark px-3 rounded-pill">Pendiente</span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-center">
                                    <button type="button" class="btn btn-sm btn-action px-3" data-bs-toggle="modal" data-bs-target="#modalResp<?= $m['id_mensaje'] ?>">
                                        <i class="bi bi-reply-fill me-1"></i> <?= $m['respuesta_gestor'] ? 'EDITAR' : 'RESPONDER' ?>
                                    </button>
                                </td>
                            </tr>

                            <div class="modal fade" id="modalResp<?= $m['id_mensaje'] ?>" tabindex="-1" aria-hidden="true">
                                <div class="modal-dialog modal-dialog-centered">
                                    <div class="modal-content shadow-lg">
                                        <div class="modal-header border-secondary">
                                            <h6 class="modal-title text-gold fw-bold">Responder a <?= htmlspecialchars($m['nombre']) ?></h6>
                                            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                                        </div>
                                        <form method="POST">
                                            <div class="modal-body">
                                                <input type="hidden" name="id_mensaje" value="<?= $m['id_mensaje'] ?>">
                                                
                                                <label class="small text-gold fw-bold mb-2">MENSAJE DEL CLIENTE:</label>
                                                <div class="p-3 bg-black border border-secondary rounded mb-4 text-white small shadow-inner">
                                                    <?= htmlspecialchars($m['mensaje']) ?>
                                                </div>

                                                <label class="small text-gold fw-bold mb-2">TU RESPUESTA:</label>
                                                <textarea name="respuesta" class="form-control shadow-sm" rows="5" placeholder="Escribe aquí tu respuesta..." required><?= htmlspecialchars($m['respuesta_gestor']) ?></textarea>
                                            </div>
                                            <div class="modal-footer border-0">
                                                <button type="submit" name="responder" class="btn w-100 fw-bold text-dark" style="background:#D4AF37;">ENVIAR RESPUESTA AHORA</button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'footer.php'; ?>