<?php
session_start();
require 'php/config.php';

// Verificar si el usuario está logueado
if (!isset($_SESSION['usuario_id'])) {
    header("Location: index.php");
    exit;
}

// Verificar si el carrito tiene productos
if (empty($_SESSION['carrito'])) {
    header("Location: carrito.php");
    exit;
}

$uid = $_SESSION['usuario_id'];
$carrito = $_SESSION['carrito'];
$total = 0;

foreach ($carrito as $item) {
    $total += $item['precio'] * $item['cantidad'];
}

// Obtener direcciones del usuario
// Comprobar si existe columna facturacion (reutilizando lógica de perfil.php)
$use_facturacion_column = false;
try {
    $colstmt = $pdo->prepare("SELECT COUNT(*) AS cnt FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'direcciones' AND COLUMN_NAME = 'facturacion'");
    $colstmt->execute();
    $colinfo = $colstmt->fetch();
    if ($colinfo && !empty($colinfo['cnt'])) {
        $use_facturacion_column = true;
    }
} catch (Exception $e) {
    $use_facturacion_column = false;
}

if ($use_facturacion_column) {
    // Direcciones de Envío (facturacion = 0 or NULL)
    $stmt = $pdo->prepare("SELECT d.id_direccion, d.nombre, d.apellido, d.calle, d.ciudad, d.codigo_postal, d.provincia, d.pais
        FROM usuarios_direcciones ud
        JOIN direcciones d ON ud.id_direccion = d.id_direccion
        WHERE ud.id_usuario = :id AND (d.facturacion IS NULL OR d.facturacion = 0)");
    $stmt->execute(['id' => $uid]);
    $direccionesEnvio = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Direcciones de Facturación (facturacion = 1)
    $stmt = $pdo->prepare("SELECT d.id_direccion, d.nombre, d.apellido, d.calle, d.ciudad, d.codigo_postal, d.provincia, d.pais
        FROM usuarios_direcciones ud
        JOIN direcciones d ON ud.id_direccion = d.id_direccion
        WHERE ud.id_usuario = :id AND d.facturacion = 1");
    $stmt->execute(['id' => $uid]);
    $direccionesFacturacion = $stmt->fetchAll(PDO::FETCH_ASSOC);
} else {
    // Fallback: Todas las direcciones sirven para todo
    $stmt = $pdo->prepare("SELECT d.id_direccion, d.nombre, d.apellido, d.calle, d.ciudad, d.codigo_postal, d.provincia, d.pais
        FROM usuarios_direcciones ud
        JOIN direcciones d ON ud.id_direccion = d.id_direccion
        WHERE ud.id_usuario = :id");
    $stmt->execute(['id' => $uid]);
    $allDirs = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $direccionesEnvio = $allDirs;
    $direccionesFacturacion = $allDirs;
}

?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Finalizar Compra | Ryujin</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />
    <link rel="icon" href="assets/Logo.png" type="image/png" />
</head>

<body>
    <header>
        <div class="logo"><a href="index.php"><img src="assets/Logo.png" alt="logo"></a></div>
    </header>

    <main class="checkout-page">
        <h2>Finalizar Compra</h2>

        <?php if (isset($_SESSION['mensaje_error'])): ?>
            <div class="mensaje-error" style="color: red; margin-bottom: 20px;">
                <?php echo htmlspecialchars($_SESSION['mensaje_error']); unset($_SESSION['mensaje_error']); ?>
            </div>
        <?php endif; ?>

        <div class="checkout-container">
            
            <!-- Resumen del pedido -->
            <div class="resumen-pedido">
                <h3>Resumen del Pedido</h3>
                <table class="tabla-carrito">
                    <thead>
                        <tr>
                            <th>Producto</th>
                            <th>Cant.</th>
                            <th>Subtotal</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($carrito as $item): ?>
                            <tr>
                                <td>
                                    <div class="producto-checkout">
                                        <img src="/tfg<?php echo htmlspecialchars($item['imagen']); ?>" alt="<?php echo htmlspecialchars($item['nombre']); ?>">
                                        <span><?php echo htmlspecialchars($item['nombre']); ?></span>
                                    </div>
                                </td>
                                <td><?php echo $item['cantidad']; ?></td>
                                <td><?php echo number_format($item['precio'] * $item['cantidad'], 2, ',', '.'); ?> €</td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                <p class="total" style="font-size: 1.2em; font-weight: bold; margin-top: 20px;">
                    Total a pagar: <?php echo number_format($total, 2, ',', '.'); ?> €
                </p>
            </div>

            <!-- Selección de dirección -->
            <div class="seleccion-direccion">
                <form action="php/crea_pedido.php" method="POST" id="form-checkout">
                    <!-- Sección Envío -->
                    <h3>Dirección de envío</h3>
                    <div class="form-group">
                        <select name="id_direccion" id="select-envio" class="form-control" required onchange="checkNewAddress('envio')">
                            <option value="">Selecciona una dirección de envío...</option>
                            <?php foreach ($direccionesEnvio as $dir): ?>
                                <option value="<?php echo $dir['id_direccion']; ?>">
                                    <?php echo htmlspecialchars($dir['nombre'] . ' ' . $dir['apellido'] . ' - ' . $dir['calle'] . ', ' . $dir['ciudad']); ?>
                                </option>
                            <?php endforeach; ?>
                            <option value="new">+ Añadir nueva dirección de envío...</option>
                        </select>
                    </div>

                    <!-- Sección Facturación -->
                    <h3 style="margin-top: 20px;">Dirección de facturación</h3>
                    <div class="form-group">
                        <select name="id_direccion_facturacion" id="select-facturacion" class="form-control" required onchange="checkNewAddress('facturacion')">
                            <option value="">Selecciona una dirección de facturación...</option>
                            <?php foreach ($direccionesFacturacion as $dir): ?>
                                <option value="<?php echo $dir['id_direccion']; ?>">
                                    <?php echo htmlspecialchars($dir['nombre'] . ' ' . $dir['apellido'] . ' - ' . $dir['calle'] . ', ' . $dir['ciudad']); ?>
                                </option>
                            <?php endforeach; ?>
                            <option value="new">+ Añadir nueva dirección de facturación...</option>
                        </select>
                    </div>

                    <div style="margin-top: 30px;">
                        <button type="submit" class="btn btn-confirmar">Confirmar Pedido</button>
                    </div>
                </form>

                <!-- Formularios Ocultos para añadir dirección -->
                <div id="new-address-forms" style="display: none;">
                    
                    <!-- Formulario Envío -->
                    <div id="form-new-envio" class="modal-address" style="display: none;">
                        <div class="modal-content">
                            <span class="close-modal" onclick="closeModal('envio')">&times;</span>
                            <h4>Nueva Dirección de Envío</h4>
                            <form action="php/direcciones_handler.php" method="POST">
                                <input type="hidden" name="action" value="add">
                                <input type="hidden" name="redirect" value="../checkout.php">
                                <?php if($use_facturacion_column): ?>
                                    <input type="hidden" name="facturacion" value="0">
                                <?php endif; ?>
                                
                                <input type="text" name="nombre" placeholder="Nombre" required class="input-full">
                                <input type="text" name="apellido" placeholder="Apellido" required class="input-full">
                                <input type="text" name="calle" placeholder="Calle" required class="input-full">
                                <input type="text" name="ciudad" placeholder="Ciudad" required class="input-full">
                                <input type="text" name="codigo_postal" placeholder="Código Postal" required class="input-full">
                                <input type="text" name="provincia" placeholder="Provincia" required class="input-full">
                                <input type="text" name="pais" placeholder="País" required class="input-full">
                                
                                <button type="submit" class="btn">Guardar Dirección</button>
                            </form>
                        </div>
                    </div>

                    <!-- Formulario Facturación -->
                    <div id="form-new-facturacion" class="modal-address" style="display: none;">
                        <div class="modal-content">
                            <span class="close-modal" onclick="closeModal('facturacion')">&times;</span>
                            <h4>Nueva Dirección de Facturación</h4>
                            <form action="php/direcciones_handler.php" method="POST">
                                <input type="hidden" name="action" value="add">
                                <input type="hidden" name="redirect" value="../checkout.php">
                                <?php if($use_facturacion_column): ?>
                                    <input type="hidden" name="facturacion" value="1">
                                <?php endif; ?>

                                <input type="text" name="nombre" placeholder="Nombre" required class="input-full">
                                <input type="text" name="apellido" placeholder="Apellido" required class="input-full">
                                <input type="text" name="calle" placeholder="Calle" required class="input-full">
                                <input type="text" name="ciudad" placeholder="Ciudad" required class="input-full">
                                <input type="text" name="codigo_postal" placeholder="Código Postal" required class="input-full">
                                <input type="text" name="provincia" placeholder="Provincia" required class="input-full">
                                <input type="text" name="pais" placeholder="País" required class="input-full">
                                
                                <button type="submit" class="btn">Guardar Dirección</button>
                            </form>
                        </div>
                    </div>

                </div>
            </div>

    <style>
        .form-control {
            width: 100%;
            padding: 10px;
            margin-bottom: 10px;
            border: 1px solid #ccc;
            border-radius: 4px;
        }
        .modal-address {
            position: fixed;
            top: 0; left: 0; width: 100%; height: 100%;
            background: rgba(0,0,0,0.5);
            display: flex;
            align-items: center;
            justify-content: center;
            z-index: 1000;
        }
        .modal-content {
            background: white;
            padding: 20px;
            border-radius: 8px;
            width: 90%;
            max-width: 500px;
            position: relative;
        }
        .close-modal {
            position: absolute;
            top: 10px; right: 15px;
            font-size: 24px;
            cursor: pointer;
        }
        .input-full {
            width: 100%;
            padding: 8px;
            margin: 5px 0;
            box-sizing: border-box;
        }
    </style>

    <script>
        function checkNewAddress(type) {
            var select = document.getElementById('select-' + type);
            if (select.value === 'new') {
                document.getElementById('form-new-' + type).style.display = 'flex';
                document.getElementById('new-address-forms').style.display = 'block';
                select.value = ""; // Reset select
            }
        }
        function closeModal(type) {
            document.getElementById('form-new-' + type).style.display = 'none';
        }
    </script>
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
