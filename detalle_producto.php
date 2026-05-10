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

    if (!$p) { header("Location: catalogo.php"); exit; }

    // 3. Traer imágenes de la galería
    $res_fotos = mysqli_query($conn, "SELECT * FROM productos_imagenes WHERE id_producto = '$id'");
    $galeria = [];
    $img_principal_nombre = basename($p['imagen']); 

    while($row = mysqli_fetch_assoc($res_fotos)) {
        if (basename($row['ruta_imagen']) !== $img_principal_nombre) {
            $galeria[] = $row;
        }
    }

    // 4. Traer Colores Únicos desde imágenes
    $res_colores = mysqli_query($conn, "SELECT DISTINCT color_asociado FROM productos_imagenes WHERE id_producto = '$id' AND color_asociado IS NOT NULL");
    $tiene_colores = mysqli_num_rows($res_colores) > 0;

    // ==============================================
    // NUEVO: Para botellas (id=11) traer colores desde productos_atributos
    // ==============================================
    $es_botella = ($id == 11);
    $colores_desde_atributos = [];
    if($es_botella) {
        $res_atributos = mysqli_query($conn, "SELECT DISTINCT talla as color FROM productos_atributos WHERE id_producto = '$id' AND talla IS NOT NULL AND talla != ''");
        while($attr = mysqli_fetch_assoc($res_atributos)) {
            $colores_desde_atributos[] = $attr['color'];
        }
    }

    // ==============================================
    // NUEVO: Para zapatillas (id=13) traer tallas
    // ==============================================
    $es_zapatilla = ($id == 13);
    $tallas_disponibles = [];
    if($es_zapatilla) {
        $res_tallas = mysqli_query($conn, "SELECT DISTINCT talla FROM productos_atributos WHERE id_producto = '$id' AND talla IS NOT NULL AND talla != ''");
        while($t = mysqli_fetch_assoc($res_tallas)) {
            $tallas_disponibles[] = $t['talla'];
        }
        sort($tallas_disponibles);
    }

    // 6. Traer comentarios
    $res_comentarios = mysqli_query($conn, "SELECT * FROM valoraciones WHERE id_producto = '$id' ORDER BY fecha DESC");

} else { header("Location: catalogo.php"); exit; }

include 'header.php';
include 'navbar.php';
?>

<div class="container mt-5 mb-5">
    <div class="row bg-dark shadow-lg rounded-4 p-4 mb-5" style="border: 1px solid #333;">
        <div class="col-md-6">
            <div class="p-3 rounded-4 bg-white mb-3 text-center main-img-container">
                <img id="mainImg" src="img/<?php echo $p['imagen']; ?>" class="img-fluid" style="max-height: 450px; object-fit: contain;" onerror="this.src='img/no-image.jpg';">
            </div>
            
            <div id="thumbBar" class="d-flex gap-2 overflow-auto pb-2 justify-content-center">
                <?php foreach($galeria as $foto): ?>
                    <img src="img/<?php echo $foto['ruta_imagen']; ?>" 
                         class="img-thumbnail gal-thumb <?php echo ($tiene_colores && $foto['color_asociado']) ? 'd-none' : ''; ?>" 
                         style="width: 70px; height: 70px; cursor: pointer; object-fit: cover;" 
                         data-color="<?php echo $foto['color_asociado']; ?>"
                         onclick="updateMainImg(this)"
                         onerror="this.src='img/no-image.jpg';">
                <?php endforeach; ?>
            </div>
        </div>
        
        <div class="col-md-6 ps-md-5 d-flex flex-column">
            <div class="mb-2">
                <span class="badge" style="background: rgba(212, 175, 55, 0.1); color: #D4AF37; border: 1px solid #D4AF37;">
                    <?php echo $p['nombre_categoria']; ?>
                </span>
            </div>
            
            <h1 class="fw-bold mb-2 text-white"><?php echo $p['nombre']; ?></h1>
            
            <p class="text-white mb-4" style="line-height: 1.6; opacity: 0.9;">
                <?php echo $p['descripcion']; ?>
            </p>
            
            <div class="mb-4">
                <span class="text-gold display-5 fw-bold"><?php echo $p['precio']; ?>€</span>
            </div>

            <form action="carrito.php" method="GET">
                <input type="hidden" name="id" value="<?php echo $id; ?>">
                
                <!-- ============================================== -->
                <!-- CASO 1: ZAPATILLAS (id=13) talla + color       -->
                <!-- ============================================== -->
                <?php if($es_zapatilla): ?>
                    <input type="hidden" name="atributo" value="zapatilla">
                    
                    <!-- Selector de COLOR (imágenes) -->
                    <div class="mb-3">
                        <label class="text-gold small fw-bold d-block mb-2">COLOR DISPONIBLE:</label>
                        <div class="d-flex gap-3 flex-wrap" id="colorVariantes">
                            <?php 
                            // Obtener colores únicos de las imágenes
                            $colores_zapatilla = [];
                            foreach($galeria as $foto) {
                                if($foto['color_asociado'] && !in_array($foto['color_asociado'], $colores_zapatilla)) {
                                    $colores_zapatilla[] = $foto['color_asociado'];
                                }
                            }
                            foreach($colores_zapatilla as $color): 
                            ?>
                                <div class="color-option text-center" data-color="<?php echo $color; ?>" style="cursor: pointer;">
                                    <img src="img/<?php echo $p['imagen']; ?>" 
                                         alt="<?php echo $color; ?>" 
                                         style="width: 60px; height: 60px; border-radius: 50%; border: 2px solid transparent; transition: 0.3s; object-fit: cover;"
                                         onmouseover="this.style.borderColor='#D4AF37'"
                                         onmouseout="this.style.borderColor='transparent'">
                                    <div class="small text-white-50 mt-1"><?php echo $color; ?></div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                        <input type="hidden" name="color" id="colorSeleccionado" value="">
                    </div>
                    
                    <!-- Selector de TALLA -->
                    <div class="mb-3">
                        <label class="text-gold small fw-bold d-block mb-2">TALLA:</label>
                        <select name="talla" id="tallaSeleccionada" class="form-select bg-dark text-white border-secondary shadow-none" style="width: auto; display: inline-block;" required>
                            <option value="">Selecciona una talla</option>
                            <?php foreach($tallas_disponibles as $talla): ?>
                                <option value="<?php echo $talla; ?>">Talla <?php echo $talla; ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="mb-3 p-2 rounded" style="background: #1a1a1a;" id="variantePreview">
                        <span class="text-white-50 small">Variante seleccionada: </span>
                        <span class="text-gold fw-bold" id="varianteTexto">-</span>
                    </div>
                    
                <!-- ============================================== -->
                <!-- CASO 2: BOTELLAS (id=11) color + personalización -->
                <!-- ============================================== -->
                <?php elseif($es_botella): ?>
                    <input type="hidden" name="atributo" value="color">
                    
                    <!-- Selector de COLOR (desde atributos) -->
                    <div class="mb-3">
                        <label class="text-gold small fw-bold d-block mb-2">COLOR DISPONIBLE:</label>
                        <div class="d-flex gap-3 flex-wrap" id="colorVariantesBotellas">
                            <?php foreach($colores_desde_atributos as $color): ?>
                                <div class="color-option-botella text-center" data-color="<?php echo $color; ?>" style="cursor: pointer;">
                                    <div style="width: 50px; height: 50px; border-radius: 50%; background: <?php 
                                        switch($color) {
                                            case 'Blanco': echo '#fff'; break;
                                            case 'Negro': echo '#111'; break;
                                            case 'Azul': echo '#2196F3'; break;
                                            case 'Gris': echo '#9E9E9E'; break;
                                            case 'Amarillo': echo '#FFEB3B'; break;
                                            case 'Rojo': echo '#F44336'; break;
                                            case 'Verde': echo '#4CAF50'; break;
                                            case 'Naranja': echo '#FF9800'; break;
                                            case 'Rosa': echo '#E91E63'; break;
                                            case 'Morado': echo '#9C27B0'; break;
                                            case 'Marrón': echo '#795548'; break;
                                            default: echo '#D4AF37';
                                        }
                                    ?>; border: 2px solid #333; transition: 0.3s;"></div>
                                    <div class="small text-white-50 mt-1"><?php echo $color; ?></div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                        <input type="hidden" name="color_final" id="colorFinalBotella" value="">
                    </div>
                    
                    <!-- Personalización -->
                    <?php if($p['es_personalizable'] == 1): ?>
                    <div class="mb-4 p-3 rounded-3" style="background: #1a1a1a; border: 1px dashed #D4AF37;">
                        <label class="text-gold small fw-bold">PERSONALIZACIÓN (Nombre/Empresa):</label>
                        <input type="text" name="personalizacion" class="form-control bg-transparent text-white border-secondary mt-1" placeholder="Escribe aquí el texto...">
                    </div>
                    <?php endif; ?>
                    
                    <div class="mb-3 p-2 rounded" style="background: #1a1a1a;" id="variantePreviewBotella">
                        <span class="text-white-50 small">Color seleccionado: </span>
                        <span class="text-gold fw-bold" id="varianteTextoBotella">-</span>
                    </div>
                    
                <!-- ============================================== -->
                <!-- CASO 3: PRODUCTOS CON COLORES (desde imágenes)  -->
                <!-- ============================================== -->
                <?php elseif($tiene_colores): ?>
                    <input type="hidden" name="atributo" value="color">
                    <div class="mb-3">
                        <label class="text-gold small fw-bold">COLOR DISPONIBLE:</label>
                        <select name="color" id="colorSelector" class="form-select bg-dark text-white border-secondary shadow-none" onchange="filterGallery(this.value)">
                            <option value="">Selecciona un color...</option>
                            <?php 
                            mysqli_data_seek($res_colores, 0); 
                            while($c = mysqli_fetch_assoc($res_colores)): 
                            ?>
                                <option value="<?php echo $c['color_asociado']; ?>"><?php echo $c['color_asociado']; ?></option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                    <input type="hidden" name="color_final" id="colorFinal">
                    
                    <?php if($p['es_personalizable'] == 1): ?>
                    <div class="mb-4 p-3 rounded-3" style="background: #1a1a1a; border: 1px dashed #D4AF37;">
                        <label class="text-gold small fw-bold">PERSONALIZACIÓN (Nombre/Empresa):</label>
                        <input type="text" name="personalizacion" class="form-control bg-transparent text-white border-secondary mt-1" placeholder="Escribe aquí el texto...">
                    </div>
                    <?php endif; ?>
                    
                <!-- ============================================== -->
                <!-- CASO 4: PRODUCTOS SIN ATRIBUTOS                 -->
                <!-- ============================================== -->
                <?php else: ?>
                    <input type="hidden" name="atributo" value="Única">
                    
                    <?php if($p['es_personalizable'] == 1): ?>
                    <div class="mb-4 p-3 rounded-3" style="background: #1a1a1a; border: 1px dashed #D4AF37;">
                        <label class="text-gold small fw-bold">PERSONALIZACIÓN (Nombre/Empresa):</label>
                        <input type="text" name="personalizacion" class="form-control bg-transparent text-white border-secondary mt-1" placeholder="Escribe aquí el texto...">
                    </div>
                    <?php endif; ?>
                <?php endif; ?>

                <div class="mt-4">
                    <button type="submit" class="btn btn-gold btn-lg w-100 py-3 fw-bold">
                        <i class="bi bi-cart-plus me-2"></i> AÑADIR AL CARRITO
                    </button>
                    <a href="catalogo.php" class="btn btn-link text-muted w-100 mt-2 text-decoration-none">VOLVER AL CATÁLOGO</a>
                </div>
            </form>
        </div>
    </div>

    <!-- Opiniones -->
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
                            <p class="mb-0 small text-light"><?php echo nl2br(htmlspecialchars($com['comentario'])); ?></p>
                        </div>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <div class="alert bg-dark text-muted border-secondary">Nadie ha opinado sobre este producto aún.</div>
            <?php endif; ?>
        </div>

        <div class="col-md-5">
            <div class="card p-4 shadow-sm" style="background: #151515; border: 1px solid #333;">
                <h4 class="fw-bold mb-3 text-gold">Deja tu opinión</h4>
                <?php if(isset($_SESSION['nombre'])): ?>
                    <form method="POST">
                        <div class="mb-3">
                            <label class="form-label text-white small">Puntuación:</label>
                            <select name="puntuacion" class="form-select bg-dark text-white border-secondary shadow-none" required>
                                <option value="5">★★★★★ (Excelente)</option>
                                <option value="4">★★★★☆ (Muy bueno)</option>
                                <option value="3">★★★☆☆ (Normal)</option>
                                <option value="2">★★☆☆☆ (Regular)</option>
                                <option value="1">★☆☆☆☆ (Malo)</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label text-white small">Comentario:</label>
                            <textarea name="comentario" class="form-control bg-dark text-white border-secondary shadow-none" rows="4" required></textarea>
                        </div>
                        <button type="submit" name="enviar_comentario" class="btn btn-gold w-100">PUBLICAR COMENTARIO</button>
                    </form>
                <?php else: ?>
                    <div class="text-center py-4">
                        <p class="text-light small">Debes estar identificado para dejar una opinión.</p>
                        <a href="login.php" class="btn btn-outline-gold btn-sm px-4 mt-2">INICIAR SESIÓN</a>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<script>
function updateMainImg(element) {
    if (!element) return;
    document.getElementById('mainImg').src = element.src;
    document.querySelectorAll('.img-thumbnail').forEach(t => t.classList.remove('border-gold', 'thumb-active'));
    element.classList.add('border-gold', 'thumb-active');
}

function filterGallery(color) {
    const thumbs = document.querySelectorAll('.gal-thumb');
    const mainImgBig = document.getElementById('mainImg');
    const defaultImgSrc = "img/<?php echo $p['imagen']; ?>";
    let firstVisible = null;

    if (color === "") {
        mainImgBig.src = defaultImgSrc;
        document.querySelectorAll('.img-thumbnail').forEach(t => t.classList.remove('border-gold', 'thumb-active'));
    }

    thumbs.forEach(t => {
        const thumbColor = t.getAttribute('data-color');
        if (color === "") {
            <?php if($tiene_colores): ?>
                if (!thumbColor) t.classList.remove('d-none');
                else t.classList.add('d-none');
            <?php else: ?>
                t.classList.remove('d-none');
            <?php endif; ?>
        } else {
            if (thumbColor === color) {
                t.classList.remove('d-none');
                if (!firstVisible) firstVisible = t;
            } else {
                t.classList.add('d-none');
            }
        }
    });

    if (firstVisible) {
        updateMainImg(firstVisible);
    }
    
    <?php if(!$es_zapatilla && !$es_botella && $tiene_colores): ?>
    if(document.getElementById('colorFinal')) {
        document.getElementById('colorFinal').value = color;
    }
    <?php endif; ?>
}

// ==============================================
// Para ZAPATILLAS (id=13)
// ==============================================
<?php if($es_zapatilla): ?>
document.addEventListener('DOMContentLoaded', function() {
    const colorOptions = document.querySelectorAll('#colorVariantes .color-option');
    const colorInput = document.getElementById('colorSeleccionado');
    const tallaSelect = document.getElementById('tallaSeleccionada');
    const varianteTexto = document.getElementById('varianteTexto');
    
    let colorSeleccionado = '';
    let tallaSeleccionada = '';
    
    colorOptions.forEach(opt => {
        opt.addEventListener('click', function() {
            colorOptions.forEach(c => {
                c.querySelector('img').style.borderColor = 'transparent';
            });
            this.querySelector('img').style.borderColor = '#D4AF37';
            colorSeleccionado = this.dataset.color;
            colorInput.value = colorSeleccionado;
            filterGallery(colorSeleccionado);
            actualizarVariante();
        });
    });
    
    tallaSelect.addEventListener('change', function() {
        tallaSeleccionada = this.value;
        actualizarVariante();
    });
    
    function actualizarVariante() {
        if(colorSeleccionado && tallaSeleccionada) {
            varianteTexto.innerHTML = `${colorSeleccionado} - Talla ${tallaSeleccionada}`;
        } else if(colorSeleccionado && !tallaSeleccionada) {
            varianteTexto.innerHTML = `${colorSeleccionado} - (selecciona talla)`;
        } else if(!colorSeleccionado && tallaSeleccionada) {
            varianteTexto.innerHTML = `(selecciona color) - Talla ${tallaSeleccionada}`;
        } else {
            varianteTexto.innerHTML = '-';
        }
    }
});
<?php endif; ?>

// ==============================================
// Para BOTELLAS (id=11)
// ==============================================
<?php if($es_botella): ?>
document.addEventListener('DOMContentLoaded', function() {
    const colorOptions = document.querySelectorAll('#colorVariantesBotellas .color-option-botella');
    const colorInput = document.getElementById('colorFinalBotella');
    const varianteTexto = document.getElementById('varianteTextoBotella');
    
    let colorSeleccionado = '';
    
    colorOptions.forEach(opt => {
        opt.addEventListener('click', function() {
            colorOptions.forEach(c => {
                c.querySelector('div').style.borderColor = '#333';
            });
            this.querySelector('div').style.borderColor = '#D4AF37';
            colorSeleccionado = this.dataset.color;
            colorInput.value = colorSeleccionado;
            varianteTexto.innerHTML = colorSeleccionado;
        });
    });
});
<?php endif; ?>
</script>

<style>
.text-gold { color: #D4AF37; }
.btn-gold { background-color: #D4AF37; color: black; border: none; }
.btn-gold:hover { background-color: #B8962E; color: black; }
.btn-outline-gold { border: 1px solid #D4AF37; color: #D4AF37; }
.thumb-active { border: 2px solid #D4AF37 !important; }
.img-thumbnail { background-color: #333; border: 1px solid #444; transition: 0.3s; object-fit: cover; }
.img-thumbnail:hover { border-color: #D4AF37; }
</style>

<?php include 'footer.php'; ?>