<?php
session_start();
require 'conexion.php';

// 1. Lógica de Filtrado y Búsqueda
$where = " WHERE 1=1 ";
if (isset($_GET['cat']) && !empty($_GET['cat'])) {
    $cat = mysqli_real_escape_string($conn, $_GET['cat']);
    $where .= " AND p.id_categoria = '$cat' ";
}
if (isset($_GET['buscar']) && !empty($_GET['buscar'])) {
    $buscar = mysqli_real_escape_string($conn, $_GET['buscar']);
    $where .= " AND p.nombre LIKE '%$buscar%' ";
}

// 2. Consulta con filtros aplicados
$sql = "SELECT p.*, c.nombre_categoria 
        FROM productos p 
        LEFT JOIN categorias c ON p.id_categoria = c.id_categoria 
        $where 
        ORDER BY p.id_producto DESC";
$resultado = mysqli_query($conn, $sql);

// Incluimos los componentes centralizados
include 'header.php';
include 'navbar.php';
?>

<div class="container mt-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="fw-bold text-gold">Nuestro Catálogo</h2>
        <?php if(isset($_GET['buscar']) && !empty($_GET['buscar'])): ?>
            <span class="text-muted-gold">Resultados para: "<?php echo htmlspecialchars($_GET['buscar']); ?>"</span>
        <?php endif; ?>
    </div>

    <div class="row">
        <?php if(mysqli_num_rows($resultado) > 0): ?>
            <?php while($fila = mysqli_fetch_assoc($resultado)): ?>
                <div class="col-6 col-md-4 col-lg-3 mb-4">
                    <div class="card h-100 product-card">
                        <a href="detalle_producto.php?id=<?php echo $fila['id_producto']; ?>" 
                           style="height: 200px; background: #fff; display: flex; align-items: center; justify-content: center; padding: 15px;">
                            <img src="img/<?php echo $fila['imagen']; ?>" 
                                 style="max-height: 100%; max-width: 100%; object-fit: contain;" 
                                 onerror="this.src='img/no-image.jpg';">
                        </a>
                        
                        <div class="card-body d-flex flex-column">
                            <div class="mb-2">
                                <span class="badge badge-category text-uppercase" 
                                      style="background: rgba(212, 175, 55, 0.1); color: #D4AF37; border: 1px solid #D4AF37; font-size: 0.7rem;">
                                    <?php echo $fila['nombre_categoria'] ?? 'General'; ?>
                                </span>
                            </div>
                            
                            <h6 class="fw-bold mb-2">
                                <a href="detalle_producto.php?id=<?php echo $fila['id_producto']; ?>" class="text-decoration-none text-white">
                                    <?php echo $fila['nombre']; ?>
                                </a>
                            </h6>
                            
                            <p class="text-gold fw-bold fs-5 mb-3 mt-auto"><?php echo $fila['precio']; ?>€</p>
                            
                            <a href="carrito.php?id=<?php echo $fila['id_producto']; ?>" class="btn btn-gold w-100">
                                <i class="bi bi-cart-plus me-1"></i> Comprar
                            </a>
                        </div>
                    </div>
                </div>
            <?php endwhile; ?>
        <?php else: ?>
            <div class="col-12 text-center py-5">
                <i class="bi bi-search fs-1 text-muted-gold"></i>
                <p class="mt-3 fs-5 text-white">No hemos encontrado productos con esos filtros.</p>
                <a href="catalogo.php" class="btn btn-outline-gold" style="border: 1px solid #D4AF37; color: #D4AF37;">Ver todo el catálogo</a>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php include 'footer.php'; ?>