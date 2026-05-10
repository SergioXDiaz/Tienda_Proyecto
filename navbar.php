<nav class="navbar navbar-expand-lg navbar-dark sticky-top">
    <div class="container">
        <a class="navbar-brand p-0" href="index.php">
            <img src="img/logo_nuevo.jpg" alt="Angelina Shop" style="height: 50px;">
        </a>

        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navContent">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="navContent">
            <form action="catalogo.php" method="GET" class="search-group mx-auto my-3 my-lg-0">
                <input type="text" name="buscar" class="search-input" placeholder="¿Qué estás buscando?">
                
                <select name="cat" class="search-select">
                    <option value="" selected hidden>Categorías</option>
                    <?php
                    $res_c = mysqli_query($conn, "SELECT * FROM categorias");
                    while($c = mysqli_fetch_assoc($res_c)) {
                        echo "<option value='".$c['id_categoria']."'>".$c['nombre_categoria']."</option>";
                    }
                    ?>
                </select>
                
                <button class="search-btn" type="submit">
                    <i class="bi bi-search"></i>
                </button>
            </form>

            <ul class="navbar-nav ms-auto align-items-center">
                <li class="nav-item"><a class="nav-link text-white" href="catalogo.php">Catálogo</a></li>
                
                <li class="nav-item">
                    <a class="nav-link text-white" href="contacto.php">Contacto</a>
                </li>
                
                <li class="nav-item ms-lg-3">
                    <a href="carrito.php" class="text-gold position-relative me-3">
                        <i class="bi bi-cart-fill fs-4"></i>
                        <?php if(isset($_SESSION['carrito']) && count($_SESSION['carrito']) > 0): ?>
                            <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger" style="font-size: 0.7rem;">
                                <?php echo count($_SESSION['carrito']); ?>
                            </span>
                        <?php endif; ?>
                    </a>
                </li>

                <?php if(isset($_SESSION['nombre'])): ?>
                    <li class="nav-item dropdown ms-lg-2 d-flex align-items-center">
                        <span class="text-gold fw-bold small">Hola, <?= htmlspecialchars($_SESSION['nombre']) ?></span>
                        
                        <!-- Botón Panel SOLO para admin o gestor (AHORA MÁS VISIBLE) -->
                        <?php if($_SESSION['rol'] == 'admin'): ?>
                            <a href="panel_admin.php" class="btn btn-gold btn-sm ms-2" style="font-size: 0.7rem; padding: 3px 10px;">
                                <i class="bi bi-speedometer2"></i> Panel
                            </a>
                        <?php elseif($_SESSION['rol'] == 'gestor'): ?>
                            <a href="gestion_pedidos.php" class="btn btn-gold btn-sm ms-2" style="font-size: 0.7rem; padding: 3px 10px;">
                                <i class="bi bi-speedometer2"></i> Panel
                            </a>
                        <?php endif; ?>
                        
                        <a href="logout.php" class="btn btn-outline-danger btn-sm ms-2" style="font-size: 0.7rem;">Salir</a>
                    </li>
                <?php else: ?>
                    <li class="nav-item ms-lg-2">
                        <a class="btn btn-gold btn-sm px-4" href="login.php">Iniciar Sesión</a>
                    </li>
                <?php endif; ?>
            </ul>
        </div>
    </div>
</nav>