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
    /* Globales de uniformidad */
    :root {
        --gold: #D4AF37;
        --gold-hover: #b8952e;
        --dark-card: #151515;
        --dark-border: #333;
    }

    body { font-size: 14px; background-color: #0b0b0b; color: #fff; }
    h1, h2, h3, h5 { font-weight: 700; }
    
    /* Tarjetas uniformes */
    .custom-card {
        background: var(--dark-card);
        border: 1px solid var(--dark-border);
        border-radius: 12px;
        transition: transform 0.2s;
    }

    /* Botones uniformes */
    .btn-gold { 
        background-color: var(--gold); 
        color: #000; 
        border: none; 
        font-weight: bold;
        text-transform: uppercase;
        letter-spacing: 1px;
    }
    .btn-gold:hover { background-color: var(--gold-hover); color: #000; }
    
    .btn-outline-custom {
        color: #888;
        border: 1px solid var(--dark-border);
    }
    .btn-outline-custom:hover {
        border-color: var(--gold);
        color: var(--gold);
    }

    /* Carrusel y Badge */
    .carousel-container {
        border-radius: 15px;
        overflow: hidden;
        border: 1px solid var(--dark-border);
        background: #0f0f0f;
    }
    .category-badge {
        top: 15px; left: 15px; z-index: 10;
        background: rgba(0,0,0,0.7);
        color: var(--gold);
        border: 1px solid var(--gold);
        padding: 5px 12px;
        font-size: 11px;
        text-transform: uppercase;
    }

    /* Inputs uniformes */
    .form-control-custom {
        background-color: #0f0f0f !important;
        border: 1px solid var(--dark-border) !important;
        color: white !important;
    }
    .form-control-custom:focus {
        border-color: var(--gold) !important;
        box-shadow: none;
    }

    .text-gold { color: var(--gold); }
</style>

<div class="container py-5">
    <div class="row g-4">
        <div class="col-md-6">
            <div class="position-relative">
                <span class="badge position-absolute category-badge">
                    <?php echo $p['nombre_categoria']; ?>
                </span>

                <div id="carouselProducto" class="carousel slide" data-bs-ride="carousel">
                    <div class="carousel-inner carousel-container shadow-lg">
                        <?php if(count($imagenes)>0): $first=true; foreach($imagenes as $img): ?>
                            <div class="carousel-item <?php echo $first?'active':''; ?>">
                                <div class="d-flex align-items-center justify-content-center" style="height:450px;">
                                    <img src="img/<?php echo $img; ?>" style="max-height:90%; max-width:90%; object-fit:contain;" onerror="this.src='img/no-image.jpg';">
                                </div>
                            </div>
                        <?php $first=false; endforeach; else: ?>
                            <div class="carousel-item active">
                                <div class="d-flex align-items-center justify-content-center" style="height:450px;">
                                    <img src="img/no-image.jpg" style="max-height:80%; object-fit:contain;">
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

        <div class="col-md-6 d-flex flex-column justify-content-center">
            <h1 class="text-white mb-3"><?php echo $p['nombre']; ?></h1>
            <p class="text-secondary mb-4" style="line-height: 1.6;">
                <?php echo $p['descripcion']; ?>
            </p>
            <div class="mb-5">
                <span class="text-gold h2 fw-bold"><?php echo $p['precio']; ?>€</span>
            </div>

            <div class="d-flex gap-3">
                <a href="carrito.php?id=<?php echo $p['id_producto']; ?>" class="btn btn-gold btn-lg px-5">
                    Añadir al carrito
                </a>
                <a href="catalogo.php" class="btn btn-outline-custom btn-lg px-4">
                    Volver
                </a>
            </div>
        </div>
    </div>

    <div class="row mt-5 pt-5">
        <div class="col-md-7">
            <h5 class="text-gold mb-4 text-uppercase italic" style="letter-spacing:1px;">Experiencias de clientes</h5>
            
            <?php 
            $num_comentarios = mysqli_num_rows($res_comentarios);
            if($num_comentarios > 0): 
                while($com = mysqli_fetch_assoc($res_comentarios)): 
            ?>
                <div class="card custom-card mb-3 shadow-sm">
                    <div class="card-body p-4">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <h6 class="fw-bold text-white mb-0"><?php echo $com['nombre_usuario']; ?></h6>
                            <div class="text-gold">
                                <?php for($i=1;$i<=5;$i++) echo ($i<=$com['puntuacion'])?'★':'☆'; ?>
                            </div>
                        </div>
                        <p class="text-muted mb-3" style="font-size: 12px;"><?php echo date("d M, Y", strtotime($com['fecha'])); ?></p>
                        <p class="text-light mb-0"><?php echo nl2br($com['comentario']); ?></p>
                    </div>
                </div>
            <?php 
                endwhile; 
            else: 
            ?>
                <div class="custom-card p-4 text-center">
                    <p class="text-muted mb-0">Este producto aún no tiene valoraciones. ¡Danos tu opinión!</p>
                </div>
            <?php endif; ?>
        </div>

        <div class="col-md-5">
            <div class="sticky-top" style="top: 100px;">
                <?php if(isset($_SESSION['nombre'])): ?>
                    <div class="card custom-card p-4 shadow">
                        <h5 class="text-white mb-4 text-center">Cuéntanos tu experiencia</h5>
                        <form method="POST">
                            <label class="text-muted small mb-1">Tu puntuación</label>
                            <select name="puntuacion" class="form-select form-control-custom mb-3">
                                <option value="5">★★★★★ (Excelente)</option>
                                <option value="4">★★★★☆ (Muy bueno)</option>
                                <option value="3">★★★☆☆ (Normal)</option>
                                <option value="2">★★☆☆☆ (Regular)</option>
                                <option value="1">★☆☆☆☆ (Malo)</option>
                            </select>
                            
                            <label class="text-muted small mb-1">Comentario</label>
                            <textarea name="comentario" class="form-control form-control-custom mb-4" rows="4" placeholder="¿Qué te ha parecido el producto?" required></textarea>
                            
                            <button type="submit" name="enviar_comentario" class="btn btn-gold w-100 py-3">
                                Enviar valoración
                            </button>
                        </form>
                    </div>
                <?php else: ?>
                    <div class="card custom-card p-5 text-center border-dashed">
                        <i class="bi bi-person-lock text-gold mb-3" style="font-size: 2rem;"></i>
                        <p class="text-white mb-4">Debes estar identificado para dejar una opinión.</p>
                        <a href="login.php" class="btn btn-gold w-100">Iniciar Sesión</a>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php include 'footer.php'; ?>