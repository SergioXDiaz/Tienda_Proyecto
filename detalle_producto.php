<?php
session_start();
require 'conexion.php';

if (isset($_GET['id'])) {
    $id = mysqli_real_escape_string($conn, $_GET['id']);
    
    // 1. Procesar comentario
    if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['enviar_comentario'])) {
        $nombre = $_SESSION['nombre'] ?? 'Anónimo';
        $comentario = mysqli_real_escape_string($conn, $_POST['comentario']);
        $puntuacion = intval($_POST['puntuacion']);
        
        $sql_ins = "INSERT INTO valoraciones (id_producto, nombre_usuario, comentario, puntuacion) 
                    VALUES ('$id', '$nombre', '$comentario', '$puntuacion')";
        mysqli_query($conn, $sql_ins);
        header("Location: detalle_producto.php?id=$id");
        exit;
    }

    // 2. Traer info del producto
    $res = mysqli_query($conn, "SELECT p.*, c.nombre_categoria FROM productos p 
                                JOIN categorias c ON p.id_categoria = c.id_categoria 
                                WHERE p.id_producto = '$id'");
    $p = mysqli_fetch_assoc($res);

    // 3. Traer comentarios
    $res_comentarios = mysqli_query($conn, "SELECT * FROM valoraciones WHERE id_producto = '$id' ORDER BY fecha DESC");

    if (!$p) { header("Location: catalogo.php"); exit; }
} else { header("Location: catalogo.php"); exit; }

include 'header.php';
include 'navbar.php';
?>

<div class="container mt-5 mb-5">
    <div class="row bg-dark shadow-lg rounded-4 p-4 mb-5" style="border: 1px solid #333;">
        <div class="col-md-6 text-center">
            <div class="p-3 rounded-4" style="background: white;">
                <img src="img/<?php echo $p['imagen']; ?>" class="img-fluid" style="max-height: 450px; object-fit: contain;" onerror="this.src='img/no-image.jpg';">
            </div>
        </div>
        
        <div class="col-md-6 ps-md-5 mt-4 mt-md-0 d-flex flex-column">
            <div class="mb-2">
                <span class="badge" style="background: rgba(212, 175, 55, 0.1); color: #D4AF37; border: 1px solid #D4AF37;">
                    <?php echo $p['nombre_categoria']; ?>
                </span>
            </div>
            
            <h1 class="fw-bold mb-3 text-white"><?php echo $p['nombre']; ?></h1>
            <p class="text-secondary fs-5 mb-4"><?php echo $p['descripcion']; ?></p>
            
            <div class="mb-4">
                <span class="text-gold display-4 fw-bold"><?php echo $p['precio']; ?>€</span>
            </div>

            <div class="mt-auto">
                <div class="d-grid gap-2 d-md-block">
                    <a href="carrito.php?id=<?php echo $p['id_producto']; ?>" class="btn btn-gold btn-lg px-5">
                        <i class="bi bi-cart-plus me-2"></i> AÑADIR AL CARRITO
                    </a>
                    <a href="catalogo.php" class="btn btn-outline-secondary btn-lg ms-md-2" style="color: #888; border-color: #444;">
                        VOLVER
                    </a>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-7">
            <h3 class="fw-bold mb-4 text-gold border-bottom pb-2" style="border-color: #D4AF37 !important;">Opiniones de clientes</h3>
            
            <?php if(mysqli_num_rows($res_comentarios) > 0): ?>
                <?php while($com = mysqli_fetch_assoc($res_comentarios)): ?>
                    <div class="card mb-3" style="background: #151515; border: none; border-left: 4px solid #D4AF37; color: white;">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center">
                                <h6 class="fw-bold mb-0 text-gold"><?php echo htmlspecialchars($com['nombre_usuario']); ?></h6>
                                <div style="color: #D4AF37;">
                                    <?php for($i=1; $i<=5; $i++) echo ($i<=$com['puntuacion']) ? '★' : '☆'; ?>
                                </div>
                            </div>
                            <small class="text-muted d-block mb-2"><?php echo $com['fecha']; ?></small>
                            <p class="mb-0"><?php echo nl2br(htmlspecialchars($com['comentario'])); ?></p>
                        </div>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <div class="alert bg-dark text-muted border-secondary">Nadie ha opinado sobre este producto aún. ¡Sé el primero!</div>
            <?php endif; ?>
        </div>

        <div class="col-md-5">
            <div class="card p-4 shadow-sm sticky-top" style="top: 100px; background: #151515; border: 1px solid #333;">
                <h4 class="fw-bold mb-3 text-gold">Deja tu opinión</h4>
                
                <?php if(isset($_SESSION['nombre'])): ?>
                    <form method="POST">
                        <div class="mb-3">
                            <label class="form-label text-white">Puntuación:</label>
                            <select name="puntuacion" class="form-select bg-dark text-white border-secondary shadow-none" required>
                                <option value="5">★★★★★ (Excelente)</option>
                                <option value="4">★★★★☆ (Muy bueno)</option>
                                <option value="3">★★★☆☆ (Normal)</option>
                                <option value="2">★★☆☆☆ (Regular)</option>
                                <option value="1">★☆☆☆☆ (Malo)</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label text-white">Comentario:</label>
                            <textarea name="comentario" class="form-select bg-dark text-white border-secondary shadow-none" rows="4" placeholder="¿Qué te ha parecido el producto?" required></textarea>
                        </div>
                        <button type="submit" name="enviar_comentario" class="btn btn-gold w-100">PUBLICAR COMENTARIO</button>
                    </form>
                <?php else: ?>
                    <div class="text-center py-4">
                        <p class="text-muted">Debes estar identificado para dejar una opinión.</p>
                        <a href="login.php" class="btn btn-gold btn-sm px-4">INICIAR SESIÓN</a>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php include 'footer.php'; ?>