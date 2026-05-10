<?php
session_start();
require 'conexion.php';

if (!isset($_GET['id'])) {
    header("Location: index.php");
    exit;
}

$id_pedido = intval($_GET['id']);

// Consultar datos del pedido
$sql_pedido = "SELECT p.*, u.nombre, u.email 
               FROM pedidos p 
               JOIN usuarios u ON p.id_usuario = u.id_usuario 
               WHERE p.id_pedido = $id_pedido";
$res_pedido = mysqli_query($conn, $sql_pedido);
$pedido = mysqli_fetch_assoc($res_pedido);

if (!$pedido) {
    header("Location: index.php");
    exit;
}

// Consultar detalles del pedido CON ATRIBUTOS E IMAGEN
$sql_detalles = "SELECT d.*, prod.nombre as producto_nombre, prod.imagen 
                 FROM pedido_detalle d 
                 JOIN productos prod ON d.id_producto = prod.id_producto 
                 WHERE d.id_pedido = $id_pedido";
$res_detalles = mysqli_query($conn, $sql_detalles);

include 'header.php';
include 'navbar.php';
?>

<div class="container mt-5 mb-5">
    <div class="row justify-content-center">
        <div class="col-md-10">
            <div class="card bg-dark border-gold rounded-4 shadow-lg">
                <div class="card-header bg-dark border-gold text-center py-4">
                    <i class="bi bi-check-circle-fill text-success fs-1"></i>
                    <h2 class="text-gold mt-2">¡Pedido realizado con éxito!</h2>
                    <p class="text-white-50">Gracias por tu compra, <?php echo htmlspecialchars($pedido['nombre']); ?></p>
                </div>
                <div class="card-body p-4">
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <h5 class="text-gold">Información del pedido</h5>
                            <p class="mb-1 text-white"><strong>Pedido #:</strong> <?php echo $id_pedido; ?></p>
                            <p class="mb-1 text-white"><strong>Fecha:</strong> <?php echo date('d/m/Y H:i', strtotime($pedido['fecha_pedido'])); ?></p>
                            <p class="mb-1 text-white"><strong>Estado:</strong> <span class="badge badge-pendiente"><?php echo strtoupper($pedido['estado']); ?></span></p>
                        </div>
                        <div class="col-md-6">
                            <h5 class="text-gold">Información de envío</h5>
                            <p class="mb-1 text-white"><?php echo htmlspecialchars($pedido['nombre']); ?></p>
                            <p class="mb-1 text-white"><?php echo htmlspecialchars($pedido['email']); ?></p>
                        </div>
                    </div>

                    <h5 class="text-gold mb-3">Resumen de productos</h5>
                    
                    <!-- Tabla optimizada para impresión -->
                    <div style="overflow-x: visible; width: 100%;">
                        <table class="table table-dark table-hover align-middle" style="width: 100%; border-collapse: collapse;">
                            <thead style="border-bottom: 2px solid #D4AF37;">
                                <tr>
                                    <th style="width: 60px;">Imagen</th>
                                    <th>Producto</th>
                                    <th class="text-center">Atributos</th>
                                    <th style="width: 70px;" class="text-center">Cantidad</th>
                                    <th style="width: 90px;" class="text-end">Precio</th>
                                    <th style="width: 90px;" class="text-end">Subtotal</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php 
                                $total = 0;
                                while($detalle = mysqli_fetch_assoc($res_detalles)): 
                                    $subtotal = $detalle['precio_unitario'] * $detalle['cantidad'];
                                    $total += $subtotal;
                                    
                                    $atributos = [];
                                    if($detalle['color']) $atributos[] = "🎨 {$detalle['color']}";
                                    if($detalle['talla']) $atributos[] = "📏 Talla {$detalle['talla']}";
                                    if($detalle['personalizacion']) $atributos[] = "✏️ {$detalle['personalizacion']}";
                                    $atributos_html = !empty($atributos) ? implode(' | ', $atributos) : '-';
                                    
                                    $ruta_img = 'img/' . $detalle['imagen'];
                                    if (!file_exists($ruta_img)) {
                                        $ruta_img = 'img/no-image.jpg';
                                    }
                                ?>
                                <tr>
                                    <td style="text-align: center;">
                                        <img src="<?php echo $ruta_img; ?>" width="45" height="45" style="object-fit: contain; background: white; border-radius: 5px; padding: 2px;">
                                    </td>
                                    <td>
                                        <strong><?php echo htmlspecialchars($detalle['producto_nombre']); ?></strong>
                                    </div>
                                    <td class="text-center text-gold small"><?php echo $atributos_html; ?></td>
                                    <td class="text-center"><?php echo $detalle['cantidad']; ?></td>
                                    <td class="text-end" style="white-space: nowrap;"><?php echo number_format($detalle['precio_unitario'], 2, ',', '.'); ?>€</div>
                                    <td class="text-end text-gold fw-bold" style="white-space: nowrap;"><?php echo number_format($subtotal, 2, ',', '.'); ?>€</div>
                                 </tr>
                                <?php endwhile; ?>
                            </tbody>
                            <tfoot>
                                <tr style="background: rgba(212, 175, 55, 0.1);">
                                    <td colspan="5" class="text-end fw-bold fs-5 py-3">TOTAL:</div>
                                    <td class="text-end fw-bold fs-3 text-gold" style="white-space: nowrap;"><?php echo number_format($total, 2, ',', '.'); ?>€</div>
                                 </tr>
                            </tfoot>
                        </table>
                    </div>

                    <div class="text-center mt-4">
                        <a href="catalogo.php" class="btn btn-gold px-4 py-2">
                            <i class="bi bi-arrow-left"></i> Seguir comprando
                        </a>
                        <button onclick="window.print()" class="btn btn-gold ms-2 px-4 py-2">
                            <i class="bi bi-printer"></i> Imprimir recibo
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    .badge-pendiente { background: #D4AF37; color: #000; }
    .btn-gold { 
        background-color: #D4AF37; 
        color: #000; 
        border: none; 
        transition: 0.3s;
        font-weight: 500;
    }
    .btn-gold:hover { 
        background-color: #b8960c; 
        color: #000; 
    }
    
    @media print {
        /* Ocultar elementos no deseados */
        .navbar, .btn, footer, [onclick="window.print()"] {
            display: none !important;
        }
        
        /* Forzar fondo blanco */
        body, .container, .card, .card-body, .bg-dark {
            background: white !important;
            color: black !important;
        }
        
        /* Ajustes de tabla */
        table {
            width: 100% !important;
            font-size: 11px !important;
        }
        
        th, td {
            border: 1px solid #ddd !important;
            padding: 6px !important;
        }
        
        /* Evitar que los números se partan */
        td[style*="white-space: nowrap"], th[style*="white-space: nowrap"] {
            white-space: nowrap !important;
        }
        
        /* Ajustar imágenes */
        img {
            max-width: 35px !important;
            max-height: 35px !important;
            page-break-inside: avoid !important;
        }
        
        tr {
            page-break-inside: avoid !important;
        }
        
        /* Forzar colores a negro */
        .text-gold, .fw-bold, .fs-3 {
            color: black !important;
        }
        
        /* Márgenes de página */
        @page {
            margin: 1.5cm;
            size: portrait;
        }
        
        /* Cabecera de tabla en impresión */
        .table-dark thead th {
            background: #f5f5f5 !important;
            color: black !important;
        }
        
        .table-dark tbody td {
            color: black !important;
        }
    }
</style>

<?php include 'footer.php'; ?>