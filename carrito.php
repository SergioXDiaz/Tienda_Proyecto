<?php
session_start();
require 'conexion.php';

// --- LÓGICA DEL CARRITO ---

// 1. Añadir producto (o sumar cantidad si ya existe)
if (isset($_GET['id'])) {
    $id = intval($_GET['id']);
    if (!isset($_SESSION['carrito'])) { $_SESSION['carrito'] = array(); }

    if (isset($_SESSION['carrito'][$id])) {
        $_SESSION['carrito'][$id]++;
    } else {
        $_SESSION['carrito'][$id] = 1;
    }
    header("Location: carrito.php"); exit;
}

// 2. Eliminar un producto específico
if (isset($_GET['eliminar'])) {
    $id_eliminar = intval($_GET['eliminar']);
    unset($_SESSION['carrito'][$id_eliminar]);
    header("Location: carrito.php"); exit;
}

// 3. Actualizar cantidades desde el formulario
if (isset($_POST['actualizar_cantidades'])) {
    foreach ($_POST['cantidades'] as $id => $cantidad) {
        if ($cantidad <= 0) {
            unset($_SESSION['carrito'][$id]);
        } else {
            $_SESSION['carrito'][$id] = intval($cantidad);
        }
    }
    header("Location: carrito.php"); exit;
}

// 4. Vaciar todo
if (isset($_GET['vaciar'])) {
    unset($_SESSION['carrito']);
    header("Location: carrito.php"); exit;
}

include 'header.php';
include 'navbar.php';
?>

<div class="container mt-5 mb-5">
    <h2 class="fw-bold mb-4 text-gold"><i class="bi bi-cart3"></i> Tu Cesta</h2>

    <?php if (empty($_SESSION['carrito'])): ?>
        <div class="text-center py-5 rounded-4" style="background: #151515; border: 1px solid #333;">
            <i class="bi bi-cart-x text-muted" style="font-size: 4rem;"></i>
            <h4 class="mt-3 text-white">Tu carrito está vacío</h4>
            <a href="catalogo.php" class="btn btn-gold mt-3 px-5">Ir al catálogo</a>
        </div>
    <?php else: ?>
        <form method="POST" action="carrito.php">
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
                        foreach ($_SESSION['carrito'] as $id => $cantidad): 
                            $res = mysqli_query($conn, "SELECT * FROM productos WHERE id_producto = $id");
                            if($p = mysqli_fetch_assoc($res)): 
                                $subtotal = $p['precio'] * $cantidad;
                                $total += $subtotal;
                        ?>
                        <tr style="border-bottom: 1px solid #333;">
                            <td>
                                <div class="d-flex align-items-center">
                                    <img src="img/<?php echo $p['imagen']; ?>" width="50" class="me-3 rounded bg-white">
                                    <span class="text-white"><?php echo $p['nombre']; ?></span>
                                </div>
                            </td>
                            <td style="width: 120px;">
                                <input type="number" name="cantidades[<?php echo $id; ?>]" value="<?php echo $cantidad; ?>" 
                                       class="form-control bg-dark text-white border-secondary text-center shadow-none cantidad-input"
                                       min="1" data-precio="<?php echo $p['precio']; ?>">
                            </td>
                            <td class="text-center text-muted-gold"><?php echo $p['precio']; ?>€</td>
                            <td class="text-center text-gold fw-bold subtotal"><?php echo number_format($subtotal, 2); ?>€</td>
                            <td class="text-center">
                                <a href="carrito.php?eliminar=<?php echo $id; ?>" class="btn btn-sm btn-outline-danger">
                                    <i class="bi bi-trash"></i>
                                </a>
                            </td>
                        </tr>
                        <?php endif; endforeach; ?>
                    </tbody>
                    <tfoot>
                        <tr style="background: rgba(212, 175, 55, 0.1);">
                            <td colspan="3" class="text-end fw-bold fs-5 py-4 text-white">TOTAL A PAGAR:</td>
                            <td class="text-center fw-bold fs-3 text-gold py-4" id="total-carrito"><?php echo number_format($total, 2); ?>€</td>
                            <td></td>
                        </tr>
                    </tfoot>
                </table>
                
                <div class="d-flex flex-wrap justify-content-between mt-4 gap-3">
                    <div>
                        <a href="carrito.php?vaciar=1" class="btn btn-outline-danger me-2">Vaciar Cesta</a>
                        <button type="submit" name="actualizar_cantidades" class="btn btn-outline-gold">
                            <i class="bi bi-arrow-clockwise"></i> Actualizar Cantidades
                        </button>
                    </div>
                    <a href="pagar.php" class="btn btn-gold px-5 fw-bold">FINALIZAR PEDIDO <i class="bi bi-arrow-right"></i></a>
                </div>
            </div>
        </form>
    <?php endif; ?>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const cantidadInputs = document.querySelectorAll('.cantidad-input');
    const totalCarrito = document.getElementById('total-carrito');

    function actualizarSubtotalYTotal() {
        let total = 0;
        cantidadInputs.forEach(input => {
            const cantidad = parseInt(input.value);
            const precio = parseFloat(input.dataset.precio);
            const subtotal = cantidad * precio;
            input.closest('tr').querySelector('.subtotal').textContent = subtotal.toFixed(2) + '€';
            total += subtotal;
        });
        totalCarrito.textContent = total.toFixed(2) + '€';
    }

    cantidadInputs.forEach(input => {
        input.addEventListener('input', actualizarSubtotalYTotal);
    });
});
</script>

<?php include 'footer.php'; ?>