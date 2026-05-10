<?php
session_start();
require 'conexion.php';

// --- LÓGICA DEL CARRITO ---

// 1. Añadir producto con atributos
if (isset($_GET['id'])) {
    $id = intval($_GET['id']);
    if (!isset($_SESSION['carrito'])) { $_SESSION['carrito'] = array(); }
    
    // Recoger atributos del producto
    $color = isset($_GET['color']) ? $_GET['color'] : (isset($_GET['color_final']) ? $_GET['color_final'] : null);
    $talla = isset($_GET['talla']) ? $_GET['talla'] : null;
    $personalizacion = isset($_GET['personalizacion']) ? $_GET['personalizacion'] : null;
    $atributo = isset($_GET['atributo']) ? $_GET['atributo'] : 'Única';
    
    // Crear clave única para el producto con sus atributos
    $clave_unica = $id;
    if ($color) $clave_unica .= '_' . $color;
    if ($talla) $clave_unica .= '_' . $talla;
    if ($personalizacion) $clave_unica .= '_' . md5($personalizacion);
    
    // Guardar en carrito con atributos
    if (isset($_SESSION['carrito'][$clave_unica])) {
        $_SESSION['carrito'][$clave_unica]['cantidad']++;
    } else {
        $_SESSION['carrito'][$clave_unica] = [
            'id_producto' => $id,
            'cantidad' => 1,
            'color' => $color,
            'talla' => $talla,
            'personalizacion' => $personalizacion,
            'atributo' => $atributo
        ];
    }
    header("Location: carrito.php"); exit;
}

// 2. Eliminar un producto específico (por clave)
if (isset($_GET['eliminar'])) {
    $clave_eliminar = $_GET['eliminar'];
    unset($_SESSION['carrito'][$clave_eliminar]);
    header("Location: carrito.php"); exit;
}

// 3. Vaciar todo
if (isset($_GET['vaciar'])) {
    unset($_SESSION['carrito']);
    header("Location: carrito.php"); exit;
}

include 'header.php';
include 'navbar.php';
?>

<style>
    .modal-gold .modal-content {
        background: #151515;
        border: 2px solid #D4AF37;
        border-radius: 15px;
    }
    .modal-gold .modal-header {
        border-bottom-color: #333;
    }
    .modal-gold .modal-title {
        color: #D4AF37;
        font-weight: bold;
    }
    .modal-gold .modal-body {
        color: white;
    }
    .modal-gold .btn-gold {
        background: #D4AF37;
        color: #000;
        font-weight: bold;
        border: none;
    }
    .modal-gold .btn-gold:hover {
        background: #b8960c;
    }
    .modal-gold .btn-outline-gold {
        background: transparent;
        border: 1px solid #D4AF37;
        color: #D4AF37;
    }
    .modal-gold .btn-outline-gold:hover {
        background: #D4AF37;
        color: #000;
    }
    .modal-gold .btn-danger-custom {
        background: #dc3545;
        color: white;
        border: none;
    }
    .modal-gold .btn-danger-custom:hover {
        background: #a71d2a;
    }
</style>

<div class="container mt-5 mb-5">
    <h2 class="fw-bold mb-4 text-gold"><i class="bi bi-cart3"></i> Tu Cesta</h2>

    <?php if (empty($_SESSION['carrito'])): ?>
        <div class="text-center py-5 rounded-4" style="background: #151515; border: 1px solid #333;">
            <i class="bi bi-cart-x text-muted" style="font-size: 4rem;"></i>
            <h4 class="mt-3 text-white">Tu carrito está vacío</h4>
            <a href="catalogo.php" class="btn btn-gold mt-3 px-5">Ir al catálogo</a>
        </div>
    <?php else: ?>
        <div class="table-responsive p-4 rounded-4 shadow-lg" style="background: #151515; border: 1px solid #333;">
            <table class="table table-dark table-hover align-middle mb-0">
                <thead style="border-bottom: 2px solid #D4AF37;">
                    <tr>
                        <th class="text-gold">Producto</th>
                        <th class="text-gold text-center">Cantidad</th>
                        <th class="text-gold text-center">Precio Unit.</th>
                        <th class="text-gold text-center">Subtotal</th>
                        <th class="text-gold text-center">Acción</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    $total = 0;
                    foreach ($_SESSION['carrito'] as $clave => $item): 
                        // Compatibilidad con formato antiguo (solo ID)
                        if (is_numeric($item)) {
                            $id_producto = $clave;
                            $cantidad = $item;
                            $color = null;
                            $talla = null;
                            $personalizacion = null;
                        } else {
                            $id_producto = $item['id_producto'];
                            $cantidad = $item['cantidad'];
                            $color = $item['color'];
                            $talla = $item['talla'];
                            $personalizacion = $item['personalizacion'];
                        }
                        
                        $res = mysqli_query($conn, "SELECT * FROM productos WHERE id_producto = $id_producto");
                        if($p = mysqli_fetch_assoc($res)): 
                            $subtotal = $p['precio'] * $cantidad;
                            $total += $subtotal;
                            
                            // Construir descripción de atributos
                            $atributos_texto = [];
                            if($color) $atributos_texto[] = "🎨 $color";
                            if($talla) $atributos_texto[] = "📏 Talla $talla";
                            if($personalizacion) $atributos_texto[] = "✏️ $personalizacion";
                            $detalle_atributos = !empty($atributos_texto) ? '<br><small class="text-gold">' . implode(' | ', $atributos_texto) . '</small>' : '';
                    ?>
                    <tr style="border-bottom: 1px solid #333;">
                        <td>
                            <div class="d-flex align-items-center">
                                <img src="img/<?php echo $p['imagen']; ?>" width="50" class="me-3 rounded bg-white">
                                <div>
                                    <span class="text-white"><?php echo $p['nombre']; ?></span>
                                    <?php echo $detalle_atributos; ?>
                                </div>
                            </div>
                        </td>
                        <td class="text-center">
                            <span class="badge bg-dark border border-gold text-gold px-3 py-2" style="font-size: 1rem;">
                                <?php echo $cantidad; ?>
                            </span>
                        </td>
                        <td class="text-center text-muted-gold"><?php echo $p['precio']; ?>€</td>
                        <td class="text-center text-gold fw-bold"><?php echo number_format($subtotal, 2); ?>€</td>
                        <td class="text-center">
                            <button type="button" 
                                    class="btn btn-sm btn-outline-danger" 
                                    data-bs-toggle="modal" 
                                    data-bs-target="#modalEliminarProducto"
                                    data-clave="<?php echo $clave; ?>"
                                    data-nombre="<?php echo htmlspecialchars($p['nombre']); ?>
                                    <?php if($color) echo ' (' . $color . ')'; ?>
                                    <?php if($talla) echo ' Talla ' . $talla; ?>">
                                <i class="bi bi-trash"></i> Eliminar
                            </button>
                        </td>
                    </tr>
                    <?php endif; endforeach; ?>
                </tbody>
                <tfoot>
                    <tr style="background: rgba(212, 175, 55, 0.1);">
                        <td colspan="3" class="text-end fw-bold fs-5 py-4 text-white">TOTAL A PAGAR:</td>
                        <td class="text-center fw-bold fs-3 text-gold py-4"><?php echo number_format($total, 2); ?>€</td>
                        <td></td>
                    </tr>
                </tfoot>
            </table>
            
            <div class="d-flex flex-wrap justify-content-between mt-4 gap-3">
                <div>
                    <button type="button" 
                            class="btn btn-outline-danger" 
                            data-bs-toggle="modal" 
                            data-bs-target="#modalVaciarCarrito">
                        <i class="bi bi-trash3"></i> Vaciar Cesta
                    </button>
                </div>
                <a href="finalizar_pedido.php" class="btn btn-gold px-5 fw-bold">
                    FINALIZAR PEDIDO <i class="bi bi-arrow-right"></i>
                </a>
            </div>
        </div>
    <?php endif; ?>
</div>

<!-- MODAL PARA ELIMINAR UN PRODUCTO -->
<div class="modal fade modal-gold" id="modalEliminarProducto" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Eliminar producto</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="modalEliminarBody">
                ¿Estás seguro de que deseas eliminar este producto del carrito?
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-gold" data-bs-dismiss="modal">Cancelar</button>
                <a href="#" id="confirmEliminarBtn" class="btn btn-danger-custom">Eliminar</a>
            </div>
        </div>
    </div>
</div>

<!-- MODAL PARA VACIAR EL CARRITO COMPLETO -->
<div class="modal fade modal-gold" id="modalVaciarCarrito" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Vaciar cesta</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                ¿Estás seguro de que deseas vaciar toda la cesta?<br>
                <small class="text-danger">Esta acción no se puede deshacer.</small>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-gold" data-bs-dismiss="modal">Cancelar</button>
                <a href="carrito.php?vaciar=1" class="btn btn-danger-custom">Vaciar cesta</a>
            </div>
        </div>
    </div>
</div>

<script>
    // Configurar modal para eliminar producto individual
    document.addEventListener('DOMContentLoaded', function() {
        const modalBody = document.getElementById('modalEliminarBody');
        const confirmBtn = document.getElementById('confirmEliminarBtn');
        
        const buttons = document.querySelectorAll('[data-bs-toggle="modal"][data-bs-target="#modalEliminarProducto"]');
        
        buttons.forEach(button => {
            button.addEventListener('click', function() {
                const clave = this.getAttribute('data-clave');
                const nombre = this.getAttribute('data-nombre');
                
                modalBody.innerHTML = `¿Estás seguro de que deseas eliminar <strong>${nombre}</strong> del carrito?<br><small class="text-danger">Esta acción no se puede deshacer.</small>`;
                confirmBtn.setAttribute('href', `carrito.php?eliminar=${clave}`);
            });
        });
    });
</script>

<?php include 'footer.php'; ?>