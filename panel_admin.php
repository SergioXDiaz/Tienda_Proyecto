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

// NUEVA LÓGICA: Promover/Degradar usuario
if (isset($_GET['cambiar_rol'])) {
    $id_a_cambiar = intval($_GET['cambiar_rol']);
    $nuevo_rol = mysqli_real_escape_string($conn, $_GET['nuevo_rol']);
    
    $roles_validos = ['cliente', 'gestor', 'admin'];
    if (in_array($nuevo_rol, $roles_validos)) {
        if ($id_a_cambiar != $_SESSION['id_usuario']) {
            $update = "UPDATE usuarios SET rol = '$nuevo_rol' WHERE id_usuario = $id_a_cambiar";
            mysqli_query($conn, $update);
            header("Location: panel_admin.php?status=rol_updated");
            exit;
        }
    }
}

// ============================================
// CONSULTAS DE FACTURACIÓN (Opción A)
// ============================================

// FACTURACIÓN DEL MES ACTUAL
$mes_actual = date('Y-m');
$sql_mes = "SELECT SUM(total) as total_mes FROM pedidos WHERE DATE_FORMAT(fecha_pedido, '%Y-%m') = '$mes_actual'";
$res_mes = mysqli_query($conn, $sql_mes);
$datos_mes = mysqli_fetch_assoc($res_mes);
$total_mes = $datos_mes['total_mes'] ?? 0;

// FACTURACIÓN TOTAL HISTÓRICA
$sql_total = "SELECT SUM(total) as total_historico FROM pedidos";
$res_total = mysqli_query($conn, $sql_total);
$datos_total = mysqli_fetch_assoc($res_total);
$total_historico = $datos_total['total_historico'] ?? 0;

// ============================================
// NUEVAS CONSULTAS: Gráfico + Top Clientes
// ============================================

// DATOS PARA EL GRÁFICO (últimos 6 meses)
$datos_grafico = [];
for ($i = 5; $i >= 0; $i--) {
    $mes = date('Y-m', strtotime("-$i months"));
    $nombre_mes = date('M', strtotime("-$i months"));
    $sql = "SELECT SUM(total) as total FROM pedidos WHERE DATE_FORMAT(fecha_pedido, '%Y-%m') = '$mes'";
    $res = mysqli_query($conn, $sql);
    $datos = mysqli_fetch_assoc($res);
    $datos_grafico[] = [
        'mes' => $nombre_mes,
        'total' => $datos['total'] ?? 0
    ];
}

// TOP 5 CLIENTES QUE MÁS COMPRAN
$top_clientes = mysqli_query($conn, "SELECT u.id_usuario, u.nombre, u.email, SUM(p.total) as gastado 
    FROM usuarios u 
    JOIN pedidos p ON u.id_usuario = p.id_usuario 
    GROUP BY u.id_usuario 
    ORDER BY gastado DESC LIMIT 5");

// OBTENER LISTADO DE USUARIOS
$resultado_usuarios = mysqli_query($conn, "SELECT * FROM usuarios ORDER BY 
    FIELD(rol, 'admin', 'gestor', 'cliente'), id_usuario ASC");

// CONTAR MENSAJES PENDIENTES
$total_pendientes = 0;
$check_tabla = mysqli_query($conn, "SHOW TABLES LIKE 'mensajes_contacto'");
if (mysqli_num_rows($check_tabla) > 0) {
    $msg_pendientes = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM mensajes_contacto WHERE (respuesta_gestor IS NULL OR respuesta_gestor = '')"));
    $total_pendientes = $msg_pendientes['total'] ?? 0;
}

include 'header.php'; 
?>

<style>
    body { background-color: #0b0b0b !important; color: white !important; }
    .text-gold { color: #D4AF37 !important; }
    
    .card-stats { 
        background: #151515 !important; 
        border: 1px solid #333 !important; 
        border-radius: 15px; 
        padding: 2rem 1.5rem;
        text-align: center;
    }
    .card-stats h6 { color: #ffffff !important; opacity: 0.7; font-size: 0.75rem; letter-spacing: 1.5px; margin-bottom: 10px; }
    .card-stats h2 { color: #D4AF37 !important; font-weight: bold; margin: 0; font-size: 2rem; }

    .table-luxury { 
        background: #151515 !important; 
        border: 1px solid #333 !important; 
        border-radius: 15px; 
        overflow: hidden; 
        margin-top: 20px;
    }
    .table-luxury table { margin-bottom: 0; color: white !important; }
    
    .table-luxury thead { background: #D4AF37 !important; }
    .table-luxury thead tr { background: #D4AF37 !important; }
    .table-luxury thead th { background: #D4AF37 !important; color: #000 !important; border: none; padding: 18px; font-size: 0.9rem; text-transform: uppercase; }
    
    .table-luxury tbody tr td { 
        background: transparent !important; 
        color: #e0e0e0 !important; 
        padding: 18px; 
        border-bottom: 1px solid #222; 
        vertical-align: middle;
    }
    .table-luxury tbody tr:hover td { background: #1f1f1f !important; color: #fff !important; }

    .badge-custom { padding: 6px 14px; border-radius: 4px; font-size: 0.7rem; font-weight: 800; letter-spacing: 0.5px; }
    .bg-admin { background: #D4AF37; color: black; }
    .bg-gestor { border: 1px solid #D4AF37; color: #D4AF37; background: rgba(212, 175, 55, 0.1); }
    .bg-cliente { background: #444; color: #eee; }
    
    .btn-role-action {
        background: transparent;
        border: 1px solid #D4AF37;
        color: #D4AF37;
        border-radius: 5px;
        padding: 4px 8px;
        font-size: 0.7rem;
        transition: 0.3s;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        gap: 4px;
        cursor: pointer;
    }
    .btn-role-action:hover {
        background: #D4AF37;
        color: #000;
    }
    .btn-role-action-danger {
        border-color: #dc3545;
        color: #dc3545;
    }
    .btn-role-action-danger:hover {
        background: #dc3545;
        color: #fff;
    }
    .acciones-group {
        display: flex;
        gap: 8px;
        justify-content: flex-end;
        flex-wrap: wrap;
    }
    
    /* Estilos de los modales */
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

    /* ============================================ */
    /* NUEVOS ESTILOS: Gráfico y Top Clientes       */
    /* ============================================ */
    .grafico-barras {
        display: flex;
        align-items: flex-end;
        justify-content: space-around;
        height: 200px;
        margin: 20px 0;
        gap: 15px;
    }
    .barra-item {
        flex: 1;
        text-align: center;
    }
    .barra {
        background: #D4AF37;
        border-radius: 5px 5px 0 0;
        transition: 0.3s;
        min-width: 30px;
    }
    .barra:hover {
        opacity: 0.8;
    }
    .barra-valor {
        font-size: 0.65rem;
        color: #aaa;
        margin-top: 8px;
    }
    .barra-mes {
        font-size: 0.7rem;
        color: #D4AF37;
        margin-top: 5px;
        font-weight: bold;
    }
    .top-cliente-item {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 12px 0;
        border-bottom: 1px solid #222;
    }
    .top-cliente-item:last-child {
        border-bottom: none;
    }
    .top-cliente-nombre {
        font-weight: bold;
        color: white;
    }
    .top-cliente-gastado {
        color: #D4AF37;
        font-weight: bold;
    }
    .top-cliente-email {
        font-size: 0.7rem;
        color: #aaa;
        margin-top: 2px;
    }
</style>

<div class="container mt-5 mb-5">
    <div class="text-center mb-5">
        <h1 class="fw-bold display-4">PANEL DE <span class="text-gold">CONTROL</span></h1>
        <div class="mx-auto" style="width: 80px; height: 3px; background: #D4AF37; margin-top: 15px;"></div>
    </div>

    <?php if(isset($_GET['status'])): ?>
        <div class="alert alert-dark border-gold text-gold alert-dismissible fade show" role="alert">
            <?php if($_GET['status'] == 'deleted'): ?>
                <i class="bi bi-check2-circle me-2"></i> Usuario eliminado correctamente.
            <?php elseif($_GET['status'] == 'rol_updated'): ?>
                <i class="bi bi-arrow-repeat me-2"></i> Rol de usuario actualizado correctamente.
            <?php endif; ?>
            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <!-- TARJETAS DE FACTURACIÓN (4: Mes + Total + Usuarios + Mensajes) -->
    <div class="row g-4 mb-5">
        <div class="col-md-3">
            <div class="card-stats shadow-lg">
                <i class="bi bi-calendar-week text-gold fs-1 d-block mb-2"></i>
                <h6 class="text-uppercase">Facturación <?php echo date('M Y'); ?></h6>
                <h2><?php echo number_format($total_mes, 2); ?>€</h2>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card-stats shadow-lg">
                <i class="bi bi-currency-euro text-gold fs-1 d-block mb-2"></i>
                <h6 class="text-uppercase">Total Histórico</h6>
                <h2><?php echo number_format($total_historico, 2); ?>€</h2>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card-stats shadow-lg">
                <i class="bi bi-people text-gold fs-1 d-block mb-2"></i>
                <h6 class="text-uppercase">Usuarios Registrados</h6>
                <h2><?php echo mysqli_num_rows($resultado_usuarios); ?></h2>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card-stats shadow-lg">
                <i class="bi bi-chat-dots text-gold fs-1 d-block mb-2"></i>
                <h6 class="text-uppercase">Mensajes Pendientes</h6>
                <h2><?php echo $total_pendientes; ?></h2>
            </div>
        </div>
    </div>

    <!-- ============================================ -->
    <!-- NUEVA SECCIÓN: Gráfico + Top Clientes        -->
    <!-- ============================================ -->
    <div class="row g-4 mb-5">
        
        <!-- Columna izquierda: Gráfico de ventas últimos 6 meses -->
        <div class="col-md-6">
            <div class="card-stats shadow-lg" style="padding: 1.5rem;">
                <h6 class="text-uppercase mb-3"><i class="bi bi-graph-up me-2 text-gold"></i> Ventas últimos 6 meses</h6>
                <div class="grafico-barras">
                    <?php foreach($datos_grafico as $barra): ?>
                        <?php 
                            $max_valor = max(array_column($datos_grafico, 'total')) ?: 1;
                            $altura = ($barra['total'] / $max_valor) * 150;
                        ?>
                        <div class="barra-item">
                            <div class="barra" style="height: <?php echo max(5, $altura); ?>px; width: 100%;"></div>
                            <div class="barra-valor"><?php echo number_format($barra['total'], 2); ?>€</div>
                            <div class="barra-mes"><?php echo $barra['mes']; ?></div>
                        </div>
                    <?php endforeach; ?>
                </div>
                <?php if(array_sum(array_column($datos_grafico, 'total')) == 0): ?>
                    <div class="text-center text-white-50 py-3">
                        <i class="bi bi-receipt fs-1"></i>
                        <p class="mt-2 mb-0">Sin ventas registradas en los últimos 6 meses</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
        
        <!-- Columna derecha: Top 5 clientes que más compran -->
        <div class="col-md-6">
            <div class="card-stats shadow-lg" style="padding: 1.5rem;">
                <h6 class="text-uppercase mb-3"><i class="bi bi-trophy me-2 text-gold"></i> Top Clientes (más gasto)</h6>
                <?php if(mysqli_num_rows($top_clientes) > 0): ?>
                    <?php while($cliente = mysqli_fetch_assoc($top_clientes)): ?>
                        <div class="top-cliente-item">
                            <div>
                                <span class="top-cliente-nombre"><?php echo htmlspecialchars($cliente['nombre']); ?></span>
                                <div class="top-cliente-email"><?php echo htmlspecialchars($cliente['email']); ?></div>
                            </div>
                            <div class="top-cliente-gastado">
                                <?php echo number_format($cliente['gastado'], 2); ?>€
                            </div>
                        </div>
                    <?php endwhile; ?>
                <?php else: ?>
                    <div class="text-center text-white-50 py-3">
                        <i class="bi bi-receipt fs-1"></i>
                        <p class="mt-2 mb-0">Sin pedidos registrados</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Gestión de Cuentas (título + botones) -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3 class="fw-bold m-0"><i class="bi bi-shield-lock me-2 text-gold"></i> Gestión de Cuentas</h3>
        <div class="d-flex gap-2">
            <a href="gestion_productos.php" class="btn btn-outline-warning btn-sm px-3">
                <i class="bi bi-box-seam me-1"></i> Productos
            </a>
            <a href="gestion_pedidos.php" class="btn btn-outline-warning btn-sm px-3">
                <i class="bi bi-cart-check me-1"></i> Pedidos
            </a>
            <a href="gestion_mensajes.php" class="btn btn-outline-warning btn-sm px-3">
                <i class="bi bi-chat-dots me-1"></i> Mensajes
            </a>
        </div>
    </div>

    <!-- Tabla de usuarios -->
    <div class="table-luxury shadow-lg">
        <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead>
                    <tr>
                        <th width="8%">ID</th>
                        <th width="20%">Usuario</th>
                        <th width="30%">Email</th>
                        <th width="12%" class="text-center">Rango</th>
                        <th width="30%" class="text-end">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    mysqli_data_seek($resultado_usuarios, 0);
                    while($user = mysqli_fetch_assoc($resultado_usuarios)): 
                    ?>
                        <tr>
                            <td class="text-muted">#<?php echo $user['id_usuario']; ?></td>
                            <td class="fw-bold"><?php echo htmlspecialchars($user['nombre']); ?></td>
                            <td><?php echo htmlspecialchars($user['email']); ?></td>
                            <td class="text-center">
                                <?php 
                                    $clase = ($user['rol'] == 'admin') ? 'bg-admin' : (($user['rol'] == 'gestor') ? 'bg-gestor' : 'bg-cliente');
                                ?>
                                <span class="badge-custom <?php echo $clase; ?>">
                                    <?php echo strtoupper($user['rol']); ?>
                                </span>
                            </td>
                            <td class="text-end">
                                <div class="acciones-group">
                                    <a href="gestion_mensajes.php?usuario=<?php echo $user['id_usuario']; ?>" 
                                       class="btn-role-action" title="Ver conversación">
                                        <i class="bi bi-chat-text"></i>
                                    </a>
                                    
                                    <?php if($user['id_usuario'] != $_SESSION['id_usuario']): ?>
                                        
                                        <?php if($user['rol'] == 'cliente'): ?>
                                            <button type="button" 
                                                    class="btn-role-action" 
                                                    data-bs-toggle="modal" 
                                                    data-bs-target="#modalConfirm"
                                                    data-action="promover"
                                                    data-id="<?php echo $user['id_usuario']; ?>"
                                                    data-nombre="<?php echo htmlspecialchars($user['nombre']); ?>"
                                                    data-rol="gestor"
                                                    data-texto="gestor">
                                                <i class="bi bi-arrow-up-circle"></i> Gestor
                                            </button>
                                        <?php endif; ?>
                                        
                                        <?php if($user['rol'] == 'gestor'): ?>
                                            <button type="button" 
                                                    class="btn-role-action btn-role-action-danger" 
                                                    data-bs-toggle="modal" 
                                                    data-bs-target="#modalConfirm"
                                                    data-action="degradar"
                                                    data-id="<?php echo $user['id_usuario']; ?>"
                                                    data-nombre="<?php echo htmlspecialchars($user['nombre']); ?>"
                                                    data-rol="cliente"
                                                    data-texto="cliente">
                                                <i class="bi bi-arrow-down-circle"></i> Cliente
                                            </button>
                                            <button type="button" 
                                                    class="btn-role-action" 
                                                    data-bs-toggle="modal" 
                                                    data-bs-target="#modalConfirm"
                                                    data-action="promover"
                                                    data-id="<?php echo $user['id_usuario']; ?>"
                                                    data-nombre="<?php echo htmlspecialchars($user['nombre']); ?>"
                                                    data-rol="admin"
                                                    data-texto="administrador">
                                                <i class="bi bi-star"></i> Admin
                                            </button>
                                        <?php endif; ?>
                                        
                                        <?php if($user['rol'] == 'admin' && $user['id_usuario'] != $_SESSION['id_usuario']): ?>
                                            <button type="button" 
                                                    class="btn-role-action btn-role-action-danger" 
                                                    data-bs-toggle="modal" 
                                                    data-bs-target="#modalConfirm"
                                                    data-action="degradar"
                                                    data-id="<?php echo $user['id_usuario']; ?>"
                                                    data-nombre="<?php echo htmlspecialchars($user['nombre']); ?>"
                                                    data-rol="gestor"
                                                    data-texto="gestor">
                                                <i class="bi bi-arrow-down-circle"></i> Gestor
                                            </button>
                                        <?php endif; ?>
                                        
                                        <!-- Botón ELIMINAR -->
                                        <button type="button" 
                                                class="btn-role-action btn-role-action-danger" 
                                                data-bs-toggle="modal" 
                                                data-bs-target="#modalConfirm"
                                                data-action="eliminar"
                                                data-id="<?php echo $user['id_usuario']; ?>"
                                                data-nombre="<?php echo htmlspecialchars($user['nombre']); ?>">
                                            <i class="bi bi-trash3-fill"></i>
                                        </button>
                                        
                                    <?php else: ?>
                                        <span class="text-gold small fw-bold"><i class="bi bi-person-check-fill me-1"></i> Tú</span>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- MODAL DE CONFIRMACIÓN -->
<div class="modal fade modal-gold" id="modalConfirm" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalTitle">Confirmar acción</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="modalBody">
                ¿Estás seguro de realizar esta acción?
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-gold" data-bs-dismiss="modal">Cancelar</button>
                <a href="#" id="confirmBtn" class="btn btn-gold">Confirmar</a>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const modalTitle = document.getElementById('modalTitle');
        const modalBody = document.getElementById('modalBody');
        const confirmBtn = document.getElementById('confirmBtn');
        
        const buttons = document.querySelectorAll('[data-bs-toggle="modal"][data-bs-target="#modalConfirm"]');
        
        buttons.forEach(button => {
            button.addEventListener('click', function() {
                const action = this.getAttribute('data-action');
                const id = this.getAttribute('data-id');
                const nombre = this.getAttribute('data-nombre');
                const rol = this.getAttribute('data-rol');
                const texto = this.getAttribute('data-texto');
                
                let titulo = '';
                let mensaje = '';
                let url = '';
                
                if (action === 'promover') {
                    titulo = 'Promover usuario';
                    mensaje = `¿Estás seguro de que deseas promover a <strong>${nombre}</strong> a <strong>${texto}</strong>?`;
                    url = `panel_admin.php?cambiar_rol=${id}&nuevo_rol=${rol}`;
                    confirmBtn.className = 'btn btn-gold';
                } else if (action === 'degradar') {
                    titulo = 'Degradar usuario';
                    mensaje = `¿Estás seguro de que deseas degradar a <strong>${nombre}</strong> a <strong>${texto}</strong>?`;
                    url = `panel_admin.php?cambiar_rol=${id}&nuevo_rol=${rol}`;
                    confirmBtn.className = 'btn btn-gold';
                } else if (action === 'eliminar') {
                    titulo = 'Eliminar usuario';
                    mensaje = `¿Estás seguro de que deseas eliminar a <strong>${nombre}</strong>?<br><small class="text-danger">Esta acción no se puede deshacer.</small>`;
                    url = `panel_admin.php?borrar_usuario=${id}`;
                    confirmBtn.className = 'btn btn-danger-custom';
                }
                
                modalTitle.textContent = titulo;
                modalBody.innerHTML = mensaje;
                confirmBtn.setAttribute('href', url);
            });
        });
    });
</script>

<?php include 'footer.php'; ?>