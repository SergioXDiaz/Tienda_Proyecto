<?php
// Detectar la página actual para marcar el botón activo
$pagina_actual = basename($_SERVER['PHP_SELF']);
?>
<div class="col-md-2 sidebar d-none d-md-block">
    <div class="text-center mb-4">
        <a href="index.php">
            <img src="img/logo.png" class="rounded-circle mb-2" style="height: 80px; border: 2px solid #D4AF37; object-fit: cover;">
        </a>
        <h6 class="text-gold fw-bold mb-0">ANGELINA SHOP</h6>
        <small class="text-white-50">Panel de Control</small>
        
        <?php if(isset($_SESSION['nombre'])): ?>
            <div class="mt-2" style="font-size: 0.75rem; color: #D4AF37;">
                <i class="bi bi-person-circle"></i> <?= htmlspecialchars($_SESSION['nombre']) ?>
            </div>
        <?php endif; ?>
    </div>
    <hr class="border-secondary">
    
    <!-- ============================================= -->
    <!-- BOTÓN PARA ADMIN: Volver a Panel de Control   -->
    <!-- ============================================= -->
    <?php if(isset($_SESSION['rol']) && $_SESSION['rol'] == 'admin'): ?>
        <div class="mb-3 px-2">
            <a href="panel_admin.php" class="nav-link active" style="background: #D4AF37; color: #000; border-radius: 8px; text-align: center;">
                <i class="bi bi-speedometer2 me-2"></i> Panel de Control
            </a>
        </div>
        <hr class="border-secondary">
    <?php endif; ?>
    
    <ul class="nav flex-column">
        <li class="nav-item">
            <a href="gestion_pedidos.php" class="nav-link <?= ($pagina_actual == 'gestion_pedidos.php') ? 'active' : '' ?>">
                <i class="bi bi-cart-fill me-2"></i> Pedidos
            </a>
        </li>
        <li class="nav-item">
            <a href="gestion_productos.php" class="nav-link <?= ($pagina_actual == 'gestion_productos.php') ? 'active' : '' ?>">
                <i class="bi bi-box-seam me-2"></i> Productos
            </a>
        </li>
        <li class="nav-item">
            <a href="gestion_mensajes.php" class="nav-link <?= ($pagina_actual == 'gestion_mensajes.php') ? 'active' : '' ?>">
                <i class="bi bi-chat-dots-fill me-2"></i> Mensajes
                <?php
                // Solo intentamos contar si existe la conexión
                if(isset($conn)) {
                    $res_count = mysqli_query($conn, "SELECT COUNT(*) as total FROM mensajes_contacto WHERE respuesta_gestor IS NULL OR respuesta_gestor = ''");
                    if($res_count) {
                        $count_m = mysqli_fetch_assoc($res_count);
                        if($count_m['total'] > 0): ?>
                            <span class="badge rounded-pill bg-danger ms-1" style="font-size: 0.6rem;"><?= $count_m['total'] ?></span>
                        <?php endif; 
                    }
                } ?>
            </a>
        </li>
        <li class="nav-item">
            <a href="index.php" class="nav-link">
                <i class="bi bi-eye me-2"></i> Ver Tienda
            </a>
        </li>
        <li class="mt-4">
            <a href="logout.php" class="nav-link text-danger fw-bold">
                <i class="bi bi-box-arrow-left me-2"></i> Cerrar Sesión
            </a>
        </li>
    </ul>
</div>