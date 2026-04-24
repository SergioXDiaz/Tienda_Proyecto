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

include 'header.php'; // Mantiene el <head> y estilos globales
?>

<style>
    body { background-color: #0b0b0b; color: white; }
    .sidebar { 
        min-height: 100vh; 
        background: #000; 
        border-right: 1px solid #D4AF37; 
        padding-top: 20px;
    }
    .nav-link { 
        color: #aaa; 
        padding: 12px 20px;
        transition: 0.3s;
    }
    .nav-link:hover, .nav-link.active { 
        color: #D4AF37; 
        background: rgba(212, 175, 55, 0.1); 
    }
    .card-admin {
        background: #151515;
        border: 1px solid #333;
        border-radius: 15px;
    }
    .table-luxury { color: #fff; }
    .table-luxury thead { background: #000; color: #D4AF37; border-bottom: 2px solid #D4AF37; }
    
    /* Badges Personalizados */
    .badge-pendiente { background: #D4AF37; color: #000; }
    .badge-completado { background: #198754; color: #fff; }
    .badge-anulado { background: #dc3545; color: #fff; }
    
    .btn-action {
        border: 1px solid #D4AF37;
        color: #D4AF37;
        background: transparent;
    }
    .btn-action:hover {
        background: #D4AF37;
        color: #000;
    }
</style>

<div class="container-fluid">
    <div class="row">
        <div class="col-md-2 sidebar d-none d-md-block">
            <div class="text-center mb-4">
                <a href="index.php">
                    <img src="img/logo.png" class="rounded-circle mb-2" style="height: 80px; border: 2px solid #D4AF37;">
                </a>
                <h6 class="text-gold fw-bold mb-0">ANGELINA SHOP</h6>
                <small class="text-muted">Panel de Control</small>
            </div>
            <hr class="border-secondary">
            <ul class="nav flex-column">
                <li class="nav-item">
                    <a href="gestion_pedidos.php" class="nav-link active">
                        <i class="bi bi-cart-fill me-2"></i> Pedidos
                    </a>
                </li>
                <li class="nav-item">
                    <a href="gestion_productos.php" class="nav-link">
                        <i class="bi bi-box-seam me-2"></i> Productos
                    </a>
                </li>
                <li class="nav-item">
                    <a href="index.php" class="nav-link">
                        <i class="bi bi-eye me-2"></i> Ver Tienda
                    </a>
                </li>
                <li class="mt-4">
                    <a href="logout.php" class="nav-link text-danger">
                        <i class="bi bi-box-arrow-left me-2"></i> Cerrar Sesión
                    </a>
                </li>
            </ul>
        </div>

        <div class="col-md-10 p-4 p-md-5">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2 class="fw-bold text-white mb-0">GESTIÓN DE <span class="text-gold">PEDIDOS</span></h2>
                <span class="badge border border-gold text-gold p-2">
                    <i class="bi bi-person-circle me-1"></i> <?php echo $_SESSION['nombre']; ?> (<?php echo $_SESSION['rol']; ?>)
                </span>
            </div>

            <?php if(isset($_GET['msg'])): ?>
                <div class="alert alert-dark border-gold text-gold alert-dismissible fade show" role="alert">
                    <i class="bi bi-check2-all me-2"></i> <?php echo $_GET['msg']; ?>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endif; ?>

            <div class="card card-admin shadow-lg">
                <div class="table-responsive">
                    <table class="table table-dark table-hover align-middle mb-0 table-luxury">
                        <thead>
                            <tr>
                                <th class="ps-4 py-3">ID</th>
                                <th>CLIENTE</th>
                                <th>FECHA</th>
                                <th>TOTAL</th>
                                <th>ESTADO</th>
                                <th class="text-center">ACCIONES</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while($ped = mysqli_fetch_assoc($res_pedidos)): ?>
                            <tr>
                                <td class="ps-4 fw-bold text-gold">#<?php echo $ped['id_pedido']; ?></td>
                                <td>
                                    <div class="fw-bold"><?php echo $ped['cliente']; ?></div>
                                    <small class="text-muted">ID Usuario: <?php echo $ped['id_usuario']; ?></small>
                                </td>
                                <td class="text-muted-gold"><?php echo date('d/m/Y H:i', strtotime($ped['fecha_pedido'])); ?></td>
                                <td class="fw-bold"><?php echo number_format($ped['total'], 2); ?>€</td>
                                <td>
                                    <span class="badge badge-<?php echo $ped['estado']; ?> rounded-pill px-3">
                                        <?php echo strtoupper($ped['estado']); ?>
                                    </span>
                                </td>
                                <td class="text-center">
                                    <div class="btn-group">
                                        <a href="pedido_exito.php?id=<?php echo $ped['id_pedido']; ?>" class="btn btn-sm btn-action" title="Ver Recibo">
                                            <i class="bi bi-file-earmark-text"></i>
                                        </a>
                                        
                                        <?php if($ped['estado'] == 'pendiente'): ?>
                                            <a href="gestion_pedidos.php?id_pedido=<?php echo $ped['id_pedido']; ?>&estado=completado" 
                                               class="btn btn-sm btn-action mx-1" title="Completar">
                                                <i class="bi bi-check-circle"></i>
                                            </a>
                                            <a href="gestion_pedidos.php?id_pedido=<?php echo $ped['id_pedido']; ?>&estado=anulado" 
                                               class="btn btn-sm btn-action" title="Anular" 
                                               onclick="return confirm('¿Deseas anular el pedido #<?php echo $ped['id_pedido']; ?>?')">
                                                <i class="bi bi-x-circle"></i>
                                            </a>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            </div>
            
            <p class="text-center mt-5 text-muted small text-uppercase letter-spacing-2">
                Angelina Shop Luxury Management System Suite v2.0
            </p>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>