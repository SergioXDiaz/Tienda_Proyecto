<?php
session_start();
require 'conexion.php';

// SEGURIDAD
if (!isset($_SESSION['rol']) || ($_SESSION['rol'] != 'gestor' && $_SESSION['rol'] != 'admin')) {
    header("Location: index.php");
    exit;
}

// --- LÓGICA DE ACTUALIZACIÓN ---
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['actualizar_producto'])) {
    $id_p = intval($_POST['id_producto']);
    $nuevo_precio = mysqli_real_escape_string($conn, $_POST['precio']);
    $en_oferta = intval($_POST['en_oferta']);
    $precio_anterior = !empty($_POST['precio_anterior']) ? "'".mysqli_real_escape_string($conn, $_POST['precio_anterior'])."'" : "NULL";
    
    $sql_update_prod = "UPDATE productos SET precio = '$nuevo_precio', en_oferta = $en_oferta, precio_anterior = $precio_anterior WHERE id_producto = $id_p";
    mysqli_query($conn, $sql_update_prod);

    if (isset($_POST['stock_atributo'])) {
        foreach ($_POST['stock_atributo'] as $id_atrib => $stock_val) {
            $id_atrib = intval($id_atrib);
            $stock_val = intval($stock_val);
            mysqli_query($conn, "UPDATE productos_atributos SET stock_especifico = $stock_val WHERE id_atributo = $id_atrib");
        }
        mysqli_query($conn, "UPDATE productos SET stock = (SELECT SUM(stock_especifico) FROM productos_atributos WHERE id_producto = $id_p) WHERE id_producto = $id_p");
    } else {
        $nuevo_stock = intval($_POST['stock_general']);
        mysqli_query($conn, "UPDATE productos SET stock = $nuevo_stock WHERE id_producto = $id_p");
    }
    header("Location: gestion_productos.php");
    exit;
}

// CONSULTAS
$total_productos = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM productos"))['total'];
$total_pedidos = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM pedidos WHERE estado = 'pendiente'"))['total'];
$total_valoraciones = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM valoraciones"))['total'];

$sql = "SELECT p.*, c.nombre_categoria FROM productos p LEFT JOIN categorias c ON p.id_categoria = c.id_categoria";
$res_productos = mysqli_query($conn, $sql);

$res_ultimas_val = mysqli_query($conn, "SELECT v.*, p.nombre FROM valoraciones v JOIN productos p ON v.id_producto = p.id_producto ORDER BY v.fecha DESC LIMIT 5");

include 'header.php'; 
?>

<style>
    /* Estilos específicos para la gestión de productos */
    body { background-color: #0b0b0b; color: white; }
    .text-gold { color: #D4AF37 !important; }
    .card-admin { background: #151515; border: 1px solid #333; border-radius: 15px; overflow: hidden; }
    .table-luxury thead { background: #000; color: #D4AF37; border-bottom: 2px solid #D4AF37; }
    .table-luxury tbody td { border-bottom: 1px solid #222; padding: 15px; color: white !important; }
    .btn-action { border: 1px solid #D4AF37; color: #D4AF37; background: transparent; transition: 0.3s; }
    .btn-action:hover { background: #D4AF37; color: #000; }
    .modal-content { background: #151515; border: 1px solid #D4AF37; color: white; }
    .form-control { background: #000; border: 1px solid #444; color: white; }
    
    /* Sidebar (Consistencia con navbar_gestion.php) */
    .sidebar { min-height: 100vh; background: #000; border-right: 1px solid #D4AF37; padding-top: 20px; }
    .nav-link { color: #aaa; padding: 12px 20px; transition: 0.3s; display: flex; align-items: center; text-decoration: none; }
    .nav-link:hover, .nav-link.active { color: #D4AF37; background: rgba(212, 175, 55, 0.1); border-left: 3px solid #D4AF37; }
</style>

<div class="container-fluid">
    <div class="row">
        
        <?php include 'menu_gestion.php'; ?>

        <div class="col-md-10 p-4 p-md-5">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2 class="fw-bold text-white mb-0">GESTIÓN DE <span class="text-gold">PRODUCTOS</span></h2>
                <span class="badge border border-gold text-gold p-2">
                    <i class="bi bi-person-circle me-1"></i> <?= $_SESSION['nombre']; ?> (<?= $_SESSION['rol']; ?>)
                </span>
            </div>

            <div class="row g-3 mb-4">
                <div class="col-md-4">
                    <div class="card-admin p-3 text-center">
                        <small class="text-white-50 text-uppercase">TOTAL PRODUCTOS</small>
                        <h3 class="text-gold mb-0 fw-bold"><?php echo $total_productos; ?></h3>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card-admin p-3 text-center">
                        <small class="text-white-50 text-uppercase">PENDIENTES ENVÍO</small>
                        <h3 class="text-gold mb-0 fw-bold"><?php echo $total_pedidos; ?></h3>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card-admin p-3 text-center">
                        <small class="text-white-50 text-uppercase">RESEÑAS CLIENTES</small>
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
                                            <small class="text-white-50"><?php echo $prod['nombre_categoria']; ?></small>
                                        </td>
                                        <td class="text-gold fw-bold"><?php echo number_format($prod['precio'], 2); ?>€</td>
                                        <td>
                                            <?php if($prod['stock'] <= 5): ?>
                                                <span class="badge bg-danger text-white">CRÍTICO: <?php echo $prod['stock']; ?> uds</span>
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
                                <p class="small text-white-50 my-2" style="font-style: italic;">"<?php echo $v['comentario']; ?>"</p>
                                <div class="text-info" style="font-size: 0.7rem;">Producto: <?php echo $v['nombre']; ?></div>
                            </div>
                        <?php endwhile; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php 
mysqli_data_seek($res_productos, 0); 
while($prod = mysqli_fetch_assoc($res_productos)): 
?>
<div class="modal fade" id="editModal<?php echo $prod['id_producto']; ?>" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header border-secondary">
                <h6 class="modal-title text-gold">Editar <?php echo $prod['nombre']; ?></h6>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST">
                <div class="modal-body">
                    <input type="hidden" name="id_producto" value="<?php echo $prod['id_producto']; ?>">
                    
                    <div class="row g-2 mb-3">
                        <div class="col-6">
                            <label class="small text-white-50">PRECIO (€)</label>
                            <input type="number" step="0.01" name="precio" class="form-control" value="<?php echo $prod['precio']; ?>" required>
                        </div>
                        <div class="col-6">
                            <label class="small text-white-50">¿EN OFERTA?</label>
                            <select name="en_oferta" class="form-control" style="background:#000;">
                                <option value="0" <?php echo !$prod['en_oferta'] ? 'selected' : ''; ?>>No</option>
                                <option value="1" <?php echo $prod['en_oferta'] ? 'selected' : ''; ?>>Sí</option>
                            </select>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="small text-white-50">PRECIO ANTERIOR (Opcional)</label>
                        <input type="number" step="0.01" name="precio_anterior" class="form-control" value="<?php echo $prod['precio_anterior']; ?>">
                    </div>

                    <div class="pt-2 border-top border-secondary">
                        <label class="small text-gold fw-bold mb-2 d-block">GESTIÓN DE STOCK</label>
                        <?php 
                        $idP = $prod['id_producto'];
                        $atribs = mysqli_query($conn, "SELECT * FROM productos_atributos WHERE id_producto = $idP");
                        if(mysqli_num_rows($atribs) > 0): ?>
                            <?php while($at = mysqli_fetch_assoc($atribs)): ?>
                                <div class="mb-2 d-flex align-items-center">
                                    <span class="small text-white-50 flex-grow-1"><?php echo $at['talla']; ?>:</span>
                                    <input type="number" name="stock_atributo[<?php echo $at['id_atributo']; ?>]" class="form-control w-25 text-center" value="<?php echo $at['stock_especifico']; ?>">
                                </div>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <input type="number" name="stock_general" class="form-control" value="<?php echo $prod['stock']; ?>" required>
                        <?php endif; ?>
                    </div>
                </div>
                <div class="modal-footer border-0">
                    <button type="submit" name="actualizar_producto" class="btn w-100 fw-bold text-dark" style="background:#D4AF37;">GUARDAR CAMBIOS</button>
                </div>
            </form>
        </div>
    </div>
</div>
<?php endwhile; ?>

<?php include 'footer.php'; ?>