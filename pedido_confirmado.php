<?php
session_start();
if (!isset($_GET['id_pedido'])) {
    header("Location: index.php");
    exit;
}
$id_pedido = intval($_GET['id_pedido']);
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pedido Confirmado | Ryujin</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />
    <link rel="icon" href="assets/Logo.png" type="image/png" />
</head>

<body>
    <header>
        <div class="logo"><a href="index.php"><img src="assets/Logo.png" alt="logo"></a></div>
    </header>

    <main class="pedido-confirmado-page" style="text-align: center; padding: 50px 20px;">
        <i class="fas fa-check-circle" style="font-size: 5em; color: #28a745; margin-bottom: 20px;"></i>
        <h1>¡Gracias por tu compra!</h1>
        <p style="font-size: 1.2em; margin-bottom: 30px;">Tu pedido #<?php echo $id_pedido; ?> ha sido registrado correctamente.</p>
        
        <div class="acciones">
            <a href="perfil.php" class="btn">Ver mis pedidos</a>
            <a href="index.php" class="btn" style="background-color: #555;">Volver a la tienda</a>
        </div>
    </main>

    <footer>
        <div class="logo-footer"><img src="assets/Logo.png" alt="logo" /></div>
        <div class="copy-footer">
            <p>Copyright © 2025 Ryujin. Diseñado por Javier Galán Cortés</p>
        </div>
    </footer>
</body>
</html>
