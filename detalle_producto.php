Aquí tienes el archivo completo listo para copiar y pegar. He consolidado la lógica, eliminado las partes duplicadas y organizado las columnas de opiniones exactamente como me pediste:

```php
<?php
session_start();
require 'conexion.php';

if (isset($_GET['id'])) {
    $id = mysqli_real_escape_string($conn, $_GET['id']);
    
    if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['enviar_comentario'])) {
        $nombre = $_SESSION['nombre'] ?? 'Anónimo';
        $comentario = mysqli_real_escape_string($conn, $_POST['comentario']);
        $puntuacion = intval($_POST['puntuacion']);
        
        mysqli_query($conn, "INSERT INTO valoraciones (id_producto, nombre_usuario, comentario, puntuacion) VALUES ('$id','$nombre','$comentario','$puntuacion')");
        header("Location: detalle_producto.php?id=$id");
        exit;
    }

    $res = mysqli_query($conn, "SELECT p.*, c.nombre_categoria FROM productos p JOIN categorias c ON p.id_categoria=c.id_categoria WHERE p.id_producto='$id'");
    $p = mysqli_fetch_assoc($res);

    $res_img = mysqli_query($conn, "SELECT imagen FROM producto_imagenes WHERE id_producto='$id'");
    $imagenes = [];
    while ($img = mysqli_fetch_assoc($res_img)) $imagenes[] = $img['imagen'];

    $res_comentarios = mysqli_query($conn, "SELECT * FROM valoraciones WHERE id_producto='$id' ORDER BY fecha DESC");

    if (!$p) { header("Location: catalogo.php"); exit; }
} else { header("Location: catalogo.php"); exit; }

include 'header.php';
include 'navbar.php';
?>

<style>
body{font-size:14px;}
h1{font-size:1.8rem;} h2{font-size:1.5rem;} h3{font-size:1.3rem;}
.btn{font-size:14px;}
.card{border-radius:10px;}
.text-gold { color: #D4AF37; }
.btn-gold { background-color: #D4AF37; color: #000; border: none; font-weight: bold; }
.btn-gold:hover { background-color: #b8952e; color: #000; }
</style>

<div class="container py-4">
    <div class="row">

        <div class="col-md-6">
            <div class="position-relative">
                <span class="badge position-absolute" 
                      style="top:10px;left:10px;z-index:10;background:rgba(0,0,0,0.6);color:#D4AF37;border:1px solid #D4AF37;font-size:12px;padding:6px 10px;">
                    <?php echo $p['nombre_categoria']; ?>
                </span>

                <div id="carouselProducto" class="carousel slide" data-bs-ride="carousel">
                    <div class="carousel-inner rounded-4 shadow-lg overflow-hidden" style="background:#0f0f0f;">
                        <?php if(count($imagenes)>0): $first=true; foreach($imagenes as $img): ?>
                            <div class="carousel-item <?php echo $first?'active':''; ?>">
                                <div class="d-flex align-items-center justify-content-center" style="height:45vh;min-height:280px;max-height:420px;">
                                    <img src="img/<?php echo $img; ?>" style="max-height:100%;max-width:100%;object-fit:contain;" onerror="this.src='img/no-image.jpg';">
                                </div>
                            </div>
                        <?php $first=false; endforeach; else: ?>
                            <div class="carousel-item active">
                                <div class="d-flex align-items-center justify-content-center" style="height:45vh;">
                                    <img src="img/no-image.jpg" style="max-height:100%;object-fit:contain;">
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>

                    <?php if(count($imagenes)>1): ?>
                        <button class="carousel-control-prev" type="button" data-bs-target="#carouselProducto" data-bs-slide="prev">
                            <span class="carousel-control-prev-icon"></span>
                        </button>
                        <button class="carousel-control-next" type="button" data-bs-target="#carouselProducto" data-bs-slide="next">
                            <span class="carousel-control-next-icon"></span>
                        </button>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <div class="col-md-6 ps-md-4 mt-3 mt-md-0 d-flex flex-column">
            <h2 class="fw-bold text-white mb-2"><?php echo $p['nombre']; ?></h2>
            <p class="text-secondary small mb-3"><?php echo $p['descripcion']; ?></p>
            <span class="text-gold fw-bold fs-3 mb-3"><?php echo $p['precio']; ?>€</span>

            <div class="mt-auto d-flex gap-2 flex-wrap">
                <a href="carrito.php?id=<?php echo $p['id_producto']; ?>" class="btn btn-gold px-4 py-2">
                    Añadir
                </a>
                <a href="catalogo.php" class="btn btn-outline-secondary px-3 py-2" style="color:#888;border-color:#444;">
                    Volver
                </a>
            </div>
        </div>
    </div>

    <div class="row mt-5">
        
        <div class="col-md-7">
            <h5 class="fw-bold mb-3 text-gold border-bottom pb-1">Opiniones de clientes</h5>
            <?php 
            $num_comentarios = mysqli_num_rows($res_comentarios);
            if($num_comentarios > 0): 
                while($com = mysqli_fetch_assoc($res_comentarios)): 
            ?>
                <div class="card mb-2" style="background:#151515;border-left:3px solid #D4AF37;color:white;">
                    <div class="card-body p-3">
                        <div class="d-flex justify-content-between">
                            <h6 class="fw-bold text-gold small mb-0"><?php echo $com['nombre_usuario']; ?></h6>
                            <div style="color:#D4AF37;">
                                <?php for($i=1;$i<=5;$i++) echo ($i<=$com['puntuacion'])?'★':'☆'; ?>
                            </div>
                        </div>
                        <small class="text-muted"><?php echo $com['fecha']; ?></small>
                        <p class="small mb-0"><?php echo nl2br($com['comentario']); ?></p>
                    </div>
                </div>
            <?php 
                endwhile; 
            else: 
            ?>
                <p class="text-muted small">Aún no hay opiniones sobre este producto.</p>
            <?php endif; ?>
        </div>

        <div class="col-md-5">
            <?php if(isset($_SESSION['nombre'])): ?>
                <div class="card p-3 sticky-top" style="top:80px;background:#151515;border:1px solid #333;">
                    <h5 class="fw-bold text-gold mb-2">Deja tu opinión</h5>
                    <form method="POST">
                        <select name="puntuacion" class="form-select bg-dark text-white border-secondary mb-2 small">
                            <option value="5">★★★★★</option>
                            <option value="4">★★★★☆</option>
                            <option value="3">★★★☆☆</option>
                            <option value="2">★★☆☆☆</option>
                            <option value="1">★☆☆☆☆</option>
                        </select>
                        <textarea name="comentario" class="form-control bg-dark text-white border-secondary mb-2 small" rows="3" placeholder="Escribe aquí tu opinión..." required></textarea>
                        <button type="submit" name="enviar_comentario" class="btn btn-gold w-100 py-2 small">Publicar</button>
                    </form>
                </div>
            <?php else: ?>
                <?php if($num_comentarios > 0): ?>
                    <div class="card p-4 text-center" style="background:#151515; border: 1px dashed #444;">
                        <p class="text-white small mb-3">¿Quieres dejar tu opinión? Inicia sesión para participar.</p>
                        <a href="login.php" class="btn btn-gold btn-sm">INICIAR SESIÓN</a>
                    </div>
                <?php else: ?>
                    <div class="alert bg-dark border-secondary small text-white text-center p-4">
                        <p class="mb-3">Aún nadie ha opinado. ¡Sé el primero!</p>
                        <a href="login.php" class="btn btn-gold btn-sm w-100">INICIAR SESIÓN</a>
                    </div>
                <?php endif; ?>
            <?php endif; ?>
        </div>

    </div>
</div>

<?php include 'footer.php'; ?>
