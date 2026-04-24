<?php
session_start();
require 'conexion.php';

// SEGURIDAD: Solo Gestores y Admins
if (!isset($_SESSION['rol']) || ($_SESSION['rol'] != 'gestor' && $_SESSION['rol'] != 'admin')) {
    header("Location: index.php");
    exit;
}

// LÓGICA DE ACTUALIZACIÓN DE STOCK/PRECIO
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['actualizar_producto'])) {
    $id_p = intval($_POST['id_producto']);
    $nuevo_stock = intval($_POST['stock']);
    $nuevo_precio = mysqli_real_escape_string($conn, $_POST['precio']);
    
    $sql_update = "UPDATE productos SET stock = $nuevo_stock, precio = '$nuevo_precio' WHERE id_producto = $id_p";
    mysqli_query($conn, $sql_update);
}

// Consultas para estadísticas
$total_productos = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM productos"))['total'];
$total_pedidos = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM pedidos WHERE estado = 'pendiente'"))['total'];
$total_valoraciones = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM valoraciones"))['total'];

// Listado de productos
$sql = "SELECT p.*, c.nombre_categoria FROM productos p 
        LEFT JOIN categorias c ON p.id_categoria = c.id_categoria";
$res_productos = mysqli_query($conn, $sql);

// Últimas 5 valoraciones
$res_ultimas_val = mysqli_query($conn, "SELECT v.*, p.nombre FROM valoraciones v 
                                        JOIN productos p ON v.id_producto = p.id_producto 
                                        ORDER BY v.fecha DESC LIMIT 5");

include 'header.php'; // Para mantener consistencia de fuentes y head
?>

<style>
    /* Estilos Clónicos de gestion_pedidos.php */
    body { background-color: #0b0b0b; color: white; }
    .text-gold { color: #D4AF37 !important; }
    
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
        display: flex;
        align-items: center;
    }
    .nav-link:hover, .nav-link.active { 
        color: #D4AF37; 
        background: rgba(212, 175, 55, 0.1); 
        border-left: 3px solid #D4AF37;
    }
    
    .card-admin {
        background: #151515;
        border: 1px solid #333;
        border-radius: 15px;
        overflow: hidden;
    }
    
    /* Tabla Estilo Lujo */
    .table-luxury { color: #fff; }
    .table-luxury thead { background: #000; color: #D4AF37; border-bottom: 2px solid #D4AF37; }
    .table-luxury tbody td { border-bottom: 1px solid #222; padding: 15px; background: transparent !important; color: white !important; }
    .table-hover tbody tr:hover { background: rgba(255,255,255,0.05) !important; }

    /* Botones y Badges */
    .btn-action {
        border: 1px solid #D4AF37;
        color: #D4AF37;
        background: transparent;
    }
    .btn-action:hover {
        background: #D4AF37;
        color: #000;
    }
    
    /* Modales */
    .modal-content { background: #151515; border: 1px solid #D4AF37; color: white; }
    .form-control { background: #000; border: 1px solid #444; color: white; }
    .form-control:focus { background: #050505; border-color: #D4AF37; color: white; box-shadow: none; }
</style>

<div class="container-fluid">
    <div class="row">
        <div class="col-md-2 sidebar d-none d-md-block">
            <div class="text-center mb-4">
                <a href="index.php">
                    <img src="img/logo.png" class="rounded-circle mb-2" style="height: 80px; border: 2px solid #D4AF37; object-fit: cover;">
                </a>
                <h6 class="text-gold fw-bold mb-0">ANGELINA SHOP</h6>
                <small class="text-muted">Panel de Control</small>
            </div>
            <hr class="border-secondary">
            <ul class="nav flex-column">
                <li class="nav-item">
                    <a href="gestion_pedidos.php" class="nav-link">
                        <i class="bi bi-cart-fill me-2"></i> Pedidos
                    </a>
                </li>
                <li class="nav-item">
                    <a href="gestion_productos.php" class="nav-link active">
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
                <h2 class="fw-bold text-white mb-0">GESTIÓN DE <span class="text-gold">PRODUCTOS</span></h2>
                <span class="badge border border-gold text-gold p-2">
                    <i class="bi bi-person-circle me-1"></i> <?php echo $_SESSION['nombre']; ?> (<?php echo $_SESSION['rol']; ?>)
                </span>
            </div>

            <div class="row g-3 mb-4">
                <div class="col-md-4">
                    <div class="card-admin p-3 text-center">
                        <small class="text-muted text-uppercase">Total Productos</small>
                        <h3 class="text-gold mb-0 fw-bold"><?php echo $total_productos; ?></h3>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card-admin p-3 text-center">
                        <small class="text-muted text-uppercase">Pendientes Envío</small>
                        <h3 class="text-gold mb-0 fw-bold"><?php echo $total_pedidos; ?></h3>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card-admin p-3 text-center">
                        <small class="text-muted text-uppercase">Reseñas Clientes</small>
                        <h3 class="text-gold mb-0 fw-bold"><?php echo $total_valoraciones; ?></h3>
                    </div>
                </div>
            </div>

            <div class="row g-4">
                <div class="col-lg-8">
                    <div class="card-admin shadow-lg">
                        <div class="table-responsive">
                            <table class="table table-dark table-hover align-middle mb-0 table-luxury">
                                <thead>
                                    <tr>
                                        <th class="ps-4">PRODUCTO</th>
                                        <th>PRECIO</th>
                                        <th>STOCK</th>
                                        <th class="text-center">ACCIONES</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php while($prod = mysqli_fetch_assoc($res_productos)): ?>
                                    <tr>
                                        <td class="ps-4">
                                            <div class="fw-bold text-white"><?php echo $prod['nombre']; ?></div>
                                            <small class="text-muted"><?php echo $prod['nombre_categoria']; ?></small>
                                        </td>
                                        <td class="text-gold fw-bold"><?php echo number_format($prod['precio'], 2); ?>€</td>
                                        <td>
                                            <?php if($prod['stock'] <= 5): ?>
                                                <span class="badge bg-danger text-white">CRÍTICO: <?php echo $prod['stock']; ?></span>
                                            <?php else: ?>
                                                <span class="text-white"><?php echo $prod['stock']; ?> uds</span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="text-center">
                                            <button class="btn btn-sm btn-action px-3" data-bs-toggle="modal" data-bs-target="#editModal<?php echo $prod['id_producto']; ?>">
                                                <i class="bi bi-pencil-square me-1"></i> EDITAR
                                            </button>
                                        </td>
                                    </tr>

                                    <div class="modal fade" id="editModal<?php echo $prod['id_producto']; ?>" tabindex="-1" aria-hidden="true">
                                        <div class="modal-dialog modal-sm modal-dialog-centered">
                                            <div class="modal-content">
                                                <div class="modal-header border-secondary">
                                                    <h6 class="modal-title text-gold">Ajustar Inventario</h6>
                                                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                                                </div>
                                                <form method="POST">
                                                    <div class="modal-body">
                                                        <input type="hidden" name="id_producto" value="<?php echo $prod['id_producto']; ?>">
                                                        <div class="mb-3">
                                                            <label class="small text-muted">PRECIO (€)</label>
                                                            <input type="number" step="0.01" name="precio" class="form-control" value="<?php echo $prod['precio']; ?>" required>
                                                        </div>
                                                        <div class="mb-2">
                                                            <label class="small text-muted">STOCK ACTUAL</label>
                                                            <input type="number" name="stock" class="form-control" value="<?php echo $prod['stock']; ?>" required>
                                                        </div>
                                                    </div>
                                                    <div class="modal-footer border-0">
                                                        <button type="submit" name="actualizar_producto" class="btn btn-warning w-100 fw-bold text-dark" style="background:#D4AF37; border:none;">GUARDAR</button>
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

                <div class="col-lg-4">
                    <div class="card-admin p-4">
                        <h6 class="text-gold fw-bold mb-4 border-bottom border-secondary pb-2">
                            <i class="bi bi-chat-quote me-2"></i>FEEDBACK RECIENTE
                        </h6>
                        <?php while($v = mysqli_fetch_assoc($res_ultimas_val)): ?>
                            <div class="mb-4 pb-3 border-bottom border-dark">
                                <div class="d-flex justify-content-between align-items-center">
                                    <span class="text-gold small fw-bold"><?php echo strtoupper($v['nombre_usuario']); ?></span>
                                    <span class="text-warning small"><?php echo str_repeat('★', $v['puntuacion']); ?></span>
                                </div>
                                <p class="small text-muted my-2" style="font-style: italic;">"<?php echo $v['comentario']; ?>"</p>
                                <div class="text-info" style="font-size: 0.7rem;">Producto: <?php echo $v['nombre']; ?></div>
                            </div>
                        <?php endwhile; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>