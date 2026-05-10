<?php
session_start();
require 'conexion.php';

// SEGURIDAD: Solo Gestores y Admins
if (!isset($_SESSION['rol']) || ($_SESSION['rol'] != 'gestor' && $_SESSION['rol'] != 'admin')) {
    header("Location: index.php");
    exit;
}

// --- LÓGICA DE ACCIONES ---
if (isset($_GET['id_pedido']) && isset($_GET['estado'])) {
    $id_p = intval($_GET['id_pedido']);
    $nuevo_estado = mysqli_real_escape_string($conn, $_GET['estado']);
    
    $update = "UPDATE pedidos SET estado = '$nuevo_estado' WHERE id_pedido = $id_p";
    if (mysqli_query($conn, $update)) {
        header("Location: gestion_pedidos.php?msg=Estado del pedido #$id_p actualizado a $nuevo_estado");
        exit;
    }
}

// Consultar todos los pedidos
$sql = "SELECT p.*, u.nombre as cliente 
        FROM pedidos p 
        JOIN usuarios u ON p.id_usuario = u.id_usuario 
        ORDER BY p.fecha_pedido DESC";
$res_pedidos = mysqli_query($conn, $sql);

include 'header.php'; 
?>

<style>
    /* Estilos específicos para la tabla de pedidos */
    body { background-color: #0b0b0b; color: white; }
    .text-gold { color: #D4AF37 !important; }
    .card-admin { background: #151515; border: 1px solid #333; border-radius: 15px; overflow: hidden; }
    .table-luxury thead { background: #000; color: #D4AF37; border-bottom: 2px solid #D4AF37; }
    .table-luxury thead th { background: #000 !important; color: #D4AF37 !important; border-bottom: 2px solid #D4AF37; padding: 15px; }
    .table-luxury tbody td { border-bottom: 1px solid #222; padding: 15px; color: white !important; }
    
    /* Badges de estado */
    .badge-pendiente { background: #D4AF37; color: #000; }
    .badge-completado { background: #198754; color: #fff; }
    .badge-anulado { background: #dc3545; color: #fff; }
    
    .btn-action { border: 1px solid #D4AF37; color: #D4AF37; background: transparent; transition: 0.3s; }
    .btn-action:hover { background: #D4AF37; color: #000; }
    
    /* Sidebar */
    .sidebar { min-height: 100vh; background: #000; border-right: 1px solid #D4AF37; padding-top: 20px; }
    .nav-link { color: #aaa; padding: 12px 20px; transition: 0.3s; display: flex; align-items: center; text-decoration: none; }
    .nav-link:hover, .nav-link.active { color: #D4AF37; background: rgba(212, 175, 55, 0.1); border-left: 3px solid #D4AF37; }
    
    /* Modal de confirmación (estilo dorado) */
    .modal-gold .modal-content {
        background: #151515;
        border: 2px solid #D4AF37;
        border-radius: 15px;
    }
    .modal-gold .modal-header {
        border-bottom-color: #333;
    }
    .modal-gold .modal-title {
        color: #D4AF37;
        font-weight: bold;
    }
    .modal-gold .modal-body {
        color: white;
    }
    .modal-gold .btn-gold {
        background: #D4AF37;
        color: #000;
        font-weight: bold;
        border: none;
    }
    .modal-gold .btn-gold:hover {
        background: #b8960c;
    }
    .modal-gold .btn-outline-gold {
        background: transparent;
        border: 1px solid #D4AF37;
        color: #D4AF37;
    }
    .modal-gold .btn-outline-gold:hover {
        background: #D4AF37;
        color: #000;
    }
    .modal-gold .btn-danger-custom {
        background: #dc3545;
        color: white;
        border: none;
    }
    .modal-gold .btn-danger-custom:hover {
        background: #a71d2a;
    }
    .modal-gold .btn-success-custom {
        background: #198754;
        color: white;
        border: none;
    }
    .modal-gold .btn-success-custom:hover {
        background: #0d5c36;
    }
    
    /* Badge para atributos */
    .badge-atributo {
        background: rgba(212, 175, 55, 0.1);
        border: 1px solid #D4AF37;
        color: #D4AF37;
        font-size: 0.7rem;
        padding: 3px 8px;
        margin: 2px;
        display: inline-block;
        border-radius: 5px;
    }
</style>

<div class="container-fluid">
    <div class="row">
        
        <?php include 'menu_gestion.php'; ?>

        <div class="col-md-10 p-4 p-md-5">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2 class="fw-bold text-white mb-0">GESTIÓN DE <span class="text-gold">PEDIDOS</span></h2>
                <span class="badge border border-gold text-gold p-2">
                    <i class="bi bi-person-circle me-1"></i> <?= $_SESSION['nombre']; ?> (<?= $_SESSION['rol']; ?>)
                </span>
            </div>

            <?php if(isset($_GET['msg'])): ?>
                <div class="alert alert-dark border-gold text-gold alert-dismissible fade show" role="alert">
                    <i class="bi bi-check2-all me-2"></i> <?= htmlspecialchars($_GET['msg']); ?>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endif; ?>

            <div class="card-admin shadow-lg">
                <div class="table-responsive">
                    <table class="table table-dark table-hover align-middle mb-0 table-luxury">
                        <thead>
                            <tr>
                                <th class="ps-4 py-3">ID</th>
                                <th>CLIENTE</th>
                                <th>FECHA</th>
                                <th>TOTAL</th>
                                <th>ESTADO</th>
                                <th class="text-center">DETALLE</th>
                                <th class="text-center">ACCIONES</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while($ped = mysqli_fetch_assoc($res_pedidos)): 
                                // Consultar atributos del pedido (color, talla, personalización)
                                $sql_atributos = "SELECT color, talla, personalizacion FROM pedido_detalle WHERE id_pedido = ".$ped['id_pedido']." LIMIT 1";
                                $res_atributos = mysqli_query($conn, $sql_atributos);
                                $atributos = mysqli_fetch_assoc($res_atributos);
                            ?>
                            <tr>
                                <td class="ps-4 fw-bold text-gold">#<?= $ped['id_pedido']; ?></td>
                                <td>
                                    <div class="fw-bold"><?= htmlspecialchars($ped['cliente']); ?></div>
                                    <small class="text-white-50">ID Usuario: <?= $ped['id_usuario']; ?></small>
                                </td>
                                <td class="text-white-50"><?= date('d/m/Y H:i', strtotime($ped['fecha_pedido'])); ?></td>
                                <td class="fw-bold text-white"><?= number_format($ped['total'], 2, ',', '.'); ?>€</td>
                                <td>
                                    <?php
                                        $badge_class = '';
                                        if($ped['estado'] == 'pendiente') $badge_class = 'badge-pendiente';
                                        elseif($ped['estado'] == 'completado') $badge_class = 'badge-completado';
                                        elseif($ped['estado'] == 'anulado') $badge_class = 'badge-anulado';
                                        else $badge_class = 'bg-secondary';
                                    ?>
                                    <span class="badge <?= $badge_class; ?> rounded-pill px-3">
                                        <?= strtoupper($ped['estado']); ?>
                                    </span>
                                  </td>
                                <td class="text-center">
                                    <?php if($atributos): ?>
                                        <?php if($atributos['color']): ?>
                                            <span class="badge-atributo">🎨 <?= htmlspecialchars($atributos['color']); ?></span>
                                        <?php endif; ?>
                                        <?php if($atributos['talla']): ?>
                                            <span class="badge-atributo">📏 Talla <?= htmlspecialchars($atributos['talla']); ?></span>
                                        <?php endif; ?>
                                        <?php if($atributos['personalizacion']): ?>
                                            <span class="badge-atributo">✏️ <?= htmlspecialchars(substr($atributos['personalizacion'], 0, 20)); ?></span>
                                        <?php endif; ?>
                                    <?php else: ?>
                                        <span class="text-white-50 small">-</span>
                                    <?php endif; ?>
                                 </td>
                                <td class="text-center">
                                    <div class="btn-group">
                                        <a href="pedido_exito.php?id=<?= $ped['id_pedido']; ?>" class="btn btn-sm btn-action" title="Ver Recibo">
                                            <i class="bi bi-file-earmark-text"></i>
                                        </a>
                                        
                                        <?php if($ped['estado'] == 'pendiente'): ?>
                                            <!-- Botón COMPLETAR (ahora con modal) -->
                                            <button type="button" 
                                                    class="btn btn-sm btn-action mx-1" 
                                                    title="Completar"
                                                    data-bs-toggle="modal" 
                                                    data-bs-target="#modalCompletarPedido"
                                                    data-id="<?= $ped['id_pedido']; ?>"
                                                    data-cliente="<?= htmlspecialchars($ped['cliente']); ?>">
                                                <i class="bi bi-check-circle"></i>
                                            </button>
                                            <!-- Botón ANULAR (con modal) -->
                                            <button type="button" 
                                                    class="btn btn-sm btn-action" 
                                                    title="Anular"
                                                    data-bs-toggle="modal" 
                                                    data-bs-target="#modalAnularPedido"
                                                    data-id="<?= $ped['id_pedido']; ?>"
                                                    data-cliente="<?= htmlspecialchars($ped['cliente']); ?>">
                                                <i class="bi bi-x-circle"></i>
                                            </button>
                                        <?php endif; ?>
                                    </div>
                                 </tr>
                            </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- MODAL DE CONFIRMACIÓN PARA COMPLETAR PEDIDO -->
<div class="modal fade modal-gold" id="modalCompletarPedido" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Completar pedido</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="modalCompletarBody">
                ¿Estás seguro de que deseas marcar este pedido como COMPLETADO?
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-gold" data-bs-dismiss="modal">Cancelar</button>
                <a href="#" id="confirmCompletarBtn" class="btn btn-success-custom">Completar pedido</a>
            </div>
        </div>
    </div>
</div>

<!-- MODAL DE CONFIRMACIÓN PARA ANULAR PEDIDO -->
<div class="modal fade modal-gold" id="modalAnularPedido" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Anular pedido</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="modalAnularBody">
                ¿Estás seguro de que deseas anular este pedido?
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-gold" data-bs-dismiss="modal">Cancelar</button>
                <a href="#" id="confirmAnularBtn" class="btn btn-danger-custom">Anular pedido</a>
            </div>
        </div>
    </div>
</div>

<script>
    // Configurar el modal de completado
    document.addEventListener('DOMContentLoaded', function() {
        // Modal para COMPLETAR
        const completarModalBody = document.getElementById('modalCompletarBody');
        const confirmCompletarBtn = document.getElementById('confirmCompletarBtn');
        
        const completarButtons = document.querySelectorAll('[data-bs-toggle="modal"][data-bs-target="#modalCompletarPedido"]');
        
        completarButtons.forEach(button => {
            button.addEventListener('click', function() {
                const idPedido = this.getAttribute('data-id');
                const nombreCliente = this.getAttribute('data-cliente');
                
                completarModalBody.innerHTML = `¿Estás seguro de que deseas marcar el pedido <strong>#${idPedido}</strong> de <strong>${nombreCliente}</strong> como <strong class="text-success">COMPLETADO</strong>?`;
                confirmCompletarBtn.setAttribute('href', `gestion_pedidos.php?id_pedido=${idPedido}&estado=completado`);
            });
        });
        
        // Modal para ANULAR
        const anularModalBody = document.getElementById('modalAnularBody');
        const confirmAnularBtn = document.getElementById('confirmAnularBtn');
        
        const anularButtons = document.querySelectorAll('[data-bs-toggle="modal"][data-bs-target="#modalAnularPedido"]');
        
        anularButtons.forEach(button => {
            button.addEventListener('click', function() {
                const idPedido = this.getAttribute('data-id');
                const nombreCliente = this.getAttribute('data-cliente');
                
                anularModalBody.innerHTML = `¿Estás seguro de que deseas anular el pedido <strong>#${idPedido}</strong> de <strong>${nombreCliente}</strong>?<br><small class="text-danger">Esta acción no se puede deshacer.</small>`;
                confirmAnularBtn.setAttribute('href', `gestion_pedidos.php?id_pedido=${idPedido}&estado=anulado`);
            });
        });
    });
</script>

<?php include 'footer.php'; ?>