<?php
session_start();
require 'conexion.php';

// SEGURIDAD: Solo admin
if (!isset($_SESSION['rol']) || $_SESSION['rol'] !== 'admin') {
    header("Location: index.php");
    exit;
}

// LÓGICA: Procesar el borrado de un usuario
if (isset($_GET['borrar_usuario'])) {
    $id_a_borrar = mysqli_real_escape_string($conn, $_GET['borrar_usuario']);
    if ($id_a_borrar != $_SESSION['id_usuario']) {
        mysqli_query($conn, "DELETE FROM usuarios WHERE id_usuario = '$id_a_borrar'");
        header("Location: panel_admin.php?status=deleted");
        exit;
    }
}

// OBTENER RESUMEN DE VENTAS (HISTÓRICO)
$total_dinero = 0;
$check_pedidos = mysqli_query($conn, "SHOW TABLES LIKE 'pedidos'");
if (mysqli_num_rows($check_pedidos) > 0) {
    $sql_ventas = "SELECT SUM(total) as total_historico FROM pedidos";
    $res_ventas = mysqli_query($conn, $sql_ventas);
    $datos_ventas = mysqli_fetch_assoc($res_ventas);
    $total_dinero = $datos_ventas['total_historico'] ?? 0;
}

// OBTENER LISTADO DE USUARIOS
$resultado_usuarios = mysqli_query($conn, "SELECT * FROM usuarios ORDER BY rol ASC");

include 'header.php'; 
?>

<style>
    /* Fondo general oscuro basado en tu diseño */
    body { background-color: #0b0b0b !important; color: white !important; }
    .text-gold { color: #D4AF37 !important; }
    
    /* Tarjetas de Resumen corregidas para legibilidad */
    .card-stats { 
        background: #151515 !important; 
        border: 1px solid #333 !important; 
        border-radius: 15px; 
        padding: 2.5rem;
        text-align: center;
    }
    /* Arregla visibilidad de los títulos */
    .card-stats h6 { color: #ffffff !important; opacity: 0.7; font-size: 0.85rem; letter-spacing: 1.5px; margin-bottom: 10px; }
    /* Estilo para los números de facturación y usuarios */
    .card-stats h2 { color: #D4AF37 !important; font-weight: bold; margin: 0; font-size: 2.5rem; }

    /* Tabla de Gestión (Forzado fondo oscuro para contraste) */
    .table-luxury { 
        background: #151515 !important; 
        border: 1px solid #333 !important; 
        border-radius: 15px; 
        overflow: hidden; 
        margin-top: 20px;
    }
    .table-luxury table { margin-bottom: 0; color: white !important; }
    
    /* Cabecera dorada con texto negro para máxima visibilidad */
    .table-luxury thead { background: #D4AF37 !important; }
    .table-luxury thead th { color: #000 !important; border: none; padding: 18px; font-size: 0.9rem; text-transform: uppercase; }
    
    /* Filas con texto gris claro/blanco para lectura fluida */
    .table-luxury tbody tr td { 
        background: transparent !important; 
        color: #e0e0e0 !important; 
        padding: 18px; 
        border-bottom: 1px solid #222; 
    }
    .table-luxury tbody tr:hover td { background: #1f1f1f !important; color: #fff !important; }

    /* Badges de Roles */
    .badge-custom { padding: 6px 14px; border-radius: 4px; font-size: 0.7rem; font-weight: 800; letter-spacing: 0.5px; }
    .bg-admin { background: #D4AF37; color: black; }
    .bg-gestor { border: 1px solid #D4AF37; color: #D4AF37; background: rgba(212, 175, 55, 0.1); }
    .bg-cliente { background: #444; color: #eee; }
</style>

<div class="container mt-5 mb-5">
    <div class="text-center mb-5">
        <h1 class="fw-bold display-4">PANEL DE <span class="text-gold">CONTROL</span></h1>
        <div class="mx-auto" style="width: 80px; height: 3px; background: #D4AF37; margin-top: 15px;"></div>
    </div>

    <div class="row g-4 mb-5">
        <div class="col-md-6">
            <div class="card-stats shadow-lg">
                <i class="bi bi-currency-euro text-gold fs-1 d-block mb-2"></i>
                <h6 class="text-uppercase">Facturación Total</h6>
                <h2><?php echo number_format($total_dinero, 2); ?>€</h2>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card-stats shadow-lg">
                <i class="bi bi-people text-gold fs-1 d-block mb-2"></i>
                <h6 class="text-uppercase">Usuarios Registrados</h6>
                <h2><?php echo mysqli_num_rows($resultado_usuarios); ?></h2>
            </div>
        </div>
    </div>

    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3 class="fw-bold m-0"><i class="bi bi-shield-lock me-2 text-gold"></i> Gestión de Cuentas</h3>
        <div class="d-flex gap-2">
            <a href="gestion_productos.php" class="btn btn-outline-warning btn-sm px-3">
                <i class="bi bi-box-seam me-1"></i> Productos
            </a>
            <a href="gestion_pedidos.php" class="btn btn-outline-warning btn-sm px-3">
                <i class="bi bi-cart-check me-1"></i> Pedidos
            </a>
        </div>
    </div>

    <div class="table-luxury shadow-lg">
        <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead>
                    <tr>
                        <th width="10%">ID</th>
                        <th width="25%">Usuario</th>
                        <th width="35%">Email</th>
                        <th width="15%" class="text-center">Rango</th>
                        <th width="15%" class="text-end">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while($user = mysqli_fetch_assoc($resultado_usuarios)): ?>
                        <tr>
                            <td class="text-muted">#<?php echo $user['id_usuario']; ?></td>
                            <td class="fw-bold"><?php echo $user['nombre']; ?></td>
                            <td><?php echo $user['email']; ?></td>
                            <td class="text-center">
                                <?php 
                                    $clase = ($user['rol'] == 'admin') ? 'bg-admin' : (($user['rol'] == 'gestor') ? 'bg-gestor' : 'bg-cliente');
                                ?>
                                <span class="badge-custom <?php echo $clase; ?>">
                                    <?php echo strtoupper($user['rol']); ?>
                                </span>
                            </td>
                            <td class="text-end">
                                <?php if($user['id_usuario'] != $_SESSION['id_usuario']): ?>
                                    <a href="panel_admin.php?borrar_usuario=<?php echo $user['id_usuario']; ?>" 
                                       class="text-danger fs-5 ms-2" title="Eliminar Usuario"
                                       onclick="return confirm('¿Estás seguro de que deseas eliminar este usuario?')">
                                        <i class="bi bi-trash3-fill"></i>
                                    </a>
                                <?php else: ?>
                                    <span class="text-gold small fw-bold"><i class="bi bi-person-check-fill me-1"></i> Tú</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php include 'footer.php'; ?>