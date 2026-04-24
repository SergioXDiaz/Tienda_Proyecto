<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Angelina Shop - Luxury Experience</title>
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    
    <style>
        /* FONDO GENERAL */
        body { 
            background-color: #0b0b0b; 
            background-image: radial-gradient(circle at center, #1a1a1a 0%, #000000 100%);
            min-height: 100vh; 
            display: flex; 
            flex-direction: column; 
            color: white;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        /* ELEMENTOS DORADOS */
        .text-gold { color: #D4AF37 !important; }
        .text-white { color: white !important; }
        .text-muted-gold { color: #888 !important; }

        /* BOTÓN DORADO (LOGIN Y COMPRAR) */
        .btn-gold { 
            background-color: #D4AF37 !important; 
            color: #000 !important; 
            font-weight: 800; 
            border: none; 
            border-radius: 10px; 
            padding: 12px;
            text-transform: uppercase;
            letter-spacing: 1px;
            transition: 0.3s;
        }
        .btn-gold:hover { 
            background-color: #b8952e !important; 
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(212, 175, 55, 0.3);
        }

        /* NAVBAR Y BUSCADOR (Detalle 1 y 3) */
        .navbar { 
            background-color: #000 !important; 
            border-bottom: 2px solid #D4AF37; 
        }
        
        .search-group {
            display: flex;
            background: #222;
            border: 1px solid #D4AF37;
            border-radius: 8px;
            overflow: hidden;
            width: 100%;
            max-width: 500px;
        }
        .search-input {
            border: none !important;
            background: transparent !important;
            color: white !important;
            padding: 8px 15px;
            outline: none;
            flex-grow: 1;
        }
        .search-select {
            border: none !important;
            border-left: 1px solid #444 !important;
            background: #333 !important;
            color: #D4AF37 !important;
            outline: none;
            padding: 0 10px;
            cursor: pointer;
        }
        .search-btn {
            background: #D4AF37 !important;
            border: none !important;
            color: #000 !important;
            padding: 0 15px;
            transition: 0.3s;
        }

        /* CARD DE LOGIN/REGISTRO */
        .card-luxury { 
            background: #151515; 
            border: 1px solid #D4AF37; 
            border-radius: 1.5rem; 
            box-shadow: 0 10px 50px rgba(0,0,0,0.5); 
        }

        /* TARJETAS DE PRODUCTO */
        .product-card {
            background: #1a1a1a;
            border: 1px solid #333;
            transition: 0.3s;
        }
        .product-card:hover {
            border-color: #D4AF37;
            transform: translateY(-5px);
        }
    </style>
</head>
<body class="d-flex flex-column h-100">