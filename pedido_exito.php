<?php
session_start();
require 'conexion.php';

if (!isset($_SESSION['id_usuario']) || !isset($_GET['id'])) {
    header("Location: index.php");
    exit;
}

$id_pedido = intval($_GET['id']);
$id_usuario = $_SESSION['id_usuario'];
$rol = $_SESSION['rol'] ?? 'usuario';

// --- CORRECCIÓN DE SEGURIDAD ---
// Si es admin o gestor, puede ver cualquier pedido. Si es usuario, solo el suyo.
if ($rol == 'admin' || $rol == 'gestor') {
    $sql_pedido = "SELECT * FROM pedidos WHERE id_pedido = $id_pedido";
} else {
    $sql_pedido = "SELECT * FROM pedidos WHERE id_pedido = $id_pedido AND id_usuario = $id_usuario";
}

$res_pedido = mysqli_query($conn, $sql_pedido);
$datos_pedido = mysqli_fetch_assoc($res_pedido);

if (!$datos_pedido) {
    die("Pedido no encontrado.");
}

$sql_detalle = "SELECT d.*, p.nombre, p.imagen FROM pedido_detalle d 
                JOIN productos p ON d.id_producto = p.id_producto 
                WHERE d.id_pedido = $id_pedido";
$res_detalle = mysqli_query($conn, $sql_detalle);

include 'header.php';

// Solo mostramos el navbar si NO es un gestor/admin para no ensuciar el panel de control
if ($rol != 'admin' && $rol != 'gestor') {
    include 'navbar.php'; 
}
?>

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-9 col-lg-7 text-center">
            
            <div class="mb-4">
                <div class="display-1 text-gold"><i class="bi bi-bag-check-fill"></i></div>
                <h1 class="fw-bold mt-3 text-white text-uppercase">Resumen de Pedido</h1>
                <p class="text-muted-gold">Visualizando pedido de cliente ID: <span class="text-gold"><?php echo $datos_pedido['id_usuario']; ?></span></p>
            </div>

            <div class="card bg-dark border-gold shadow-lg mb-4 text-start">
                <div class="card-body p-4 p-md-5">
                    <div class="d-flex justify-content-between border-bottom border-secondary pb-3 mb-4">
                        <div>
                            <span class="text-muted-gold d-block small mb-1">Nº DE PEDIDO</span>
                            <span class="fw-bold text-white fs-5">#<?php echo $datos_pedido['id_pedido']; ?></span>
                        </div>
                        <div class="text-end">
                            <span class="text-muted-gold d-block small mb-1">FECHA</span>
                            <span class="fw-bold text-white"><?php echo date('d/m/Y', strtotime($datos_pedido['fecha_pedido'])); ?></span>
                        </div>
                    </div>

                    <h5 class="fw-bold mb-4 text-gold">Artículos comprados</h5>
                    
                    <div class="table-responsive">
                        <table class="table table-dark table-borderless align-middle">
                            <tbody>
                                <?php while($item = mysqli_fetch_assoc($res_detalle)): ?>
                                <tr class="border-bottom border-secondary-subtle">
                                    <td class="py-3">
                                        <div class="d-flex align-items-center">
                                            <img src="img/<?php echo $item['imagen']; ?>" width="40" class="rounded me-3 bg-white p-1" onerror="this.src='img/no-image.jpg';">
                                            <span class="text-white small"><?php echo $item['nombre']; ?></span>
                                        </div>
                                    </td>
                                    <td class="text-center text-white small">x<?php echo $item['cantidad']; ?></td>
                                    <td class="text-end text-white fw-bold"><?php echo number_format($item['precio_unitario'] * $item['cantidad'], 2); ?>€</td>
                                </tr>
                                <?php endwhile; ?>
                            </tbody>
                            <tfoot>
                                <tr>
                                    <td colspan="2" class="pt-4 fw-bold fs-5 text-end text-white">TOTAL:</td>
                                    <td class="pt-4 fw-bold fs-4 text-end text-gold"><?php echo number_format($datos_pedido['total'], 2); ?>€</td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>

            <div class="d-flex flex-column flex-md-row justify-content-center gap-3 mt-4 no-print">
                <?php if ($rol == 'admin' || $rol == 'gestor'): ?>
                    <a href="gestion_pedidos.php" class="btn btn-gold px-5 fw-bold py-3 shadow">
                        VOLVER AL PANEL
                    </a>
                <?php else: ?>
                    <a href="index.php" class="btn btn-gold px-5 fw-bold py-3 shadow">
                        VOLVER A LA TIENDA
                    </a>
                <?php endif; ?>
                
                <button onclick="window.print();" class="btn btn-dark border-gold text-gold px-5 py-3 fw-bold">
                    <i class="bi bi-printer me-2"></i> IMPRIMIR RECIBO
                </button>
            </div>
        </div>
    </div>
</div>

<style>
    .border-gold { border: 2px solid #D4AF37 !important; }
    .text-gold { color: #D4AF37 !important; }
    
    @media print {
        .no-print, .navbar, .btn, .footer { display: none !important; }
        body { background-color: white !important; color: black !important; }
        .card { border: 1px solid #ccc !important; background: white !important; color: black !important; }
        .text-white, .text-gold { color: black !important; }
        .table-dark { --bs-table-bg: white !important; color: black !important; }
    }
</style>

<?php include 'footer.php'; ?>