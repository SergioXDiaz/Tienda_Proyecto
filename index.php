<?php
session_start();
require 'conexion.php';

// 1. Banners manuales
$banners_disenados = [
    ['titulo' => 'Hogar', 'imagen' => 'Banner-Hogar.jpg', 'id' => 1],
    ['titulo' => 'Informática', 'imagen' => 'Banner-Informatica.jpg', 'id' => 2],
    ['titulo' => 'Deportes', 'imagen' => 'Banner-Deportes.jpg', 'id' => 3],
    ['titulo' => 'Mascotas', 'imagen' => 'Banner-Mascotas.jpg', 'id' => 4]
];

// 2. Consultas

// Ofertas: Se queda como está por ahora según tu petición
$res_ofertas = mysqli_query($conn, "SELECT * FROM productos WHERE en_oferta = 1 LIMIT 4");

// DESTACADOS: Ahora busca la media de puntuación en la tabla 'valoraciones'
// Usamos un LEFT JOIN para que si no hay suficientes valoraciones, sigan saliendo productos
$query_destacados = "
    SELECT p.*, AVG(v.puntuacion) as media_puntos 
    FROM productos p 
    LEFT JOIN valoraciones v ON p.id_producto = v.id_producto 
    GROUP BY p.id_producto 
    ORDER BY media_puntos DESC, p.id_producto DESC 
    LIMIT 4";

$res_destacados = mysqli_query($conn, $query_destacados);

// IMPORTANTE: Incluimos los componentes
include 'header.php'; 
include 'navbar.php'; 
?>

<div id="heroCarousel" class="carousel slide container mt-4" data-bs-ride="carousel">
    <div class="carousel-inner shadow-lg" style="border-radius: 20px; overflow: hidden; border: 1px solid #D4AF37;">
        <?php $first = true; foreach($banners_disenados as $b): ?>
        <div class="carousel-item <?php echo $first ? 'active' : ''; ?>" style="height: 450px; background-color: #000;">
            <a href="catalogo.php?cat=<?php echo $b['id']; ?>">
                <img src="img/<?php echo $b['imagen']; ?>" class="d-block w-100 h-100" style="object-fit: contain;" alt="<?php echo $b['titulo']; ?>">
            </a>
        </div>
        <?php $first = false; endforeach; ?>
    </div>
    <button class="carousel-control-prev" type="button" data-bs-target="#heroCarousel" data-bs-slide="prev"><span class="carousel-control-prev-icon"></span></button>
    <button class="carousel-control-next" type="button" data-bs-target="#heroCarousel" data-bs-slide="next"><span class="carousel-control-next-icon"></span></button>
</div>

<section class="section-ofertas mt-5" style="background-color: #151515; padding: 60px 0; border-top: 1px solid #D4AF37; border-bottom: 1px solid #D4AF37;">
    <div class="container text-center">
        <h2 class="fw-bold mb-1 text-gold">¡OFERTAS FLASH!</h2>
        <p class="mb-5 fs-5 text-white">Los mejores precios de la semana</p>
        <div class="row">
            <?php while($o = mysqli_fetch_assoc($res_ofertas)): ?>
            <div class="col-md-3 mb-4">
                <div class="card product-card h-100 shadow-sm" style="background: #1a1a1a; border: 1px solid #333; border-radius: 12px;">
                    <a href="detalle_producto.php?id=<?php echo $o['id_producto']; ?>">
                        <img src="img/<?php echo $o['imagen']; ?>" class="card-img-top p-3" style="height: 180px; object-fit: contain;" onerror="this.src='img/no-image.jpg';">
                    </a>
                    <div class="card-body text-center">
                        <h6 class="fw-bold text-white"><?php echo $o['nombre']; ?></h6>
                        <p class="fw-bold fs-4 mb-3 text-gold"><?php echo $o['precio']; ?>€</p>
                        <a href="carrito.php?id=<?php echo $o['id_producto']; ?>" class="btn btn-gold w-100 text-uppercase small">Comprar</a>
                    </div>
                </div>
            </div>
            <?php endwhile; ?>
        </div>
    </div>
</section>

<section class="section-novedades py-5">
    <div class="container text-center">
        <h2 class="fw-bold mb-1 text-gold">DESTACADOS</h2>
        <p class="mb-5 fs-5 text-muted-gold">Lo más valorado por nuestros clientes</p>
        <div class="row">
            <?php while($d = mysqli_fetch_assoc($res_destacados)): ?>
            <div class="col-md-3 mb-4">
                <div class="card product-card h-100 shadow-sm" style="background: #1a1a1a; border: 1px solid #333; border-radius: 12px;">
                    <a href="detalle_producto.php?id=<?php echo $d['id_producto']; ?>">
                        <img src="img/<?php echo $d['imagen']; ?>" class="card-img-top p-3" style="height: 180px; object-fit: contain;" onerror="this.src='img/no-image.jpg';">
                    </a>
                    <div class="card-body text-center">
                        <h6 class="fw-bold text-white"><?php echo $d['nombre']; ?></h6>
                        <p class="fw-bold fs-4 mb-3 text-gold"><?php echo $d['precio']; ?>€</p>
                        <a href="detalle_producto.php?id=<?php echo $d['id_producto']; ?>" class="btn btn-gold w-100 btn-sm">Ver detalles</a>
                    </div>
                </div>
            </div>
            <?php endwhile; ?>
        </div>
    </div>
</section>

<?php include 'footer.php'; ?>