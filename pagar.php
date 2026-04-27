<?php
session_start();
require 'conexion.php';
require __DIR__ . '/vendor/autoload.php'; // <- así apunta correctamente;

// Configurar Stripe
\Stripe\Stripe::setApiKey('sk_test_STRIPE_SECRET');

// Si el carrito está vacío → fuera
if (empty($_SESSION['carrito'])) {
    header("Location: carrito.php");
    exit;
}

$line_items = [];

foreach ($_SESSION['carrito'] as $id => $cantidad) {

    $res = mysqli_query($conn, "SELECT * FROM productos WHERE id_producto = $id");

    if ($p = mysqli_fetch_assoc($res)) {

        $line_items[] = [
            'price_data' => [
                'currency' => 'eur',
                'product_data' => [
                    'name' => $p['nombre'],
                ],
                'unit_amount' => $p['precio'] * 100, // IMPORTANTE: en céntimos
            ],
            'quantity' => $cantidad,
        ];
    }
}

// Crear sesión de pago
$session = \Stripe\Checkout\Session::create([
    'payment_method_types' => ['card'],
    'line_items' => $line_items,
    'mode' => 'payment',
    'success_url' => 'https://angelinas.infinityfree.me/success.php',
    'cancel_url'  => 'https://angelinas.infinityfree.me/cancel.php',
]);

// Redirigir a Stripe
header("Location: " . $session->url);
exit;