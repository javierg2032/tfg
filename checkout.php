<?php
session_start();
require_once 'php/config.php';

// Verificar si el usuario está logueado
if (!estaLogueado()) {
    guardaMensaje('error_login', 'Inicia sesión para finalizar la compra.');
    redirige('index.php?open_cart=true');
}

// Verificar si el carrito tiene productos
if (empty($_SESSION['carrito'])) {
    redirige('carrito.php');
}

$uid = (int) $_SESSION['usuario_id'];

// Precios y stock actuales de la BD (el carrito de la sesión puede estar desactualizado)
$lineas = lineasCarrito($pdo);
$total = array_sum(array_column($lineas, 'subtotal'));
$hayProblemasStock = false;
foreach ($lineas as $linea) {
    if ($linea['cantidad'] > $linea['stock']) $hayProblemasStock = true;
}

// Direcciones del usuario separadas en envío y facturación
$stmt = $pdo->prepare("SELECT d.id_direccion, d.nombre, d.apellido, d.calle, d.ciudad, d.codigo_postal, d.provincia, d.pais, d.facturacion
    FROM usuarios_direcciones ud
    JOIN direcciones d ON ud.id_direccion = d.id_direccion
    WHERE ud.id_usuario = :id");
$stmt->execute(['id' => $uid]);
$direccionesEnvio = [];
$direccionesFacturacion = [];
foreach ($stmt->fetchAll() as $dir) {
    if (!empty($dir['facturacion'])) $direccionesFacturacion[] = $dir;
    else $direccionesEnvio[] = $dir;
}

// Métodos de pago guardados (las tarjetas caducadas no se ofrecen)
$stmt = $pdo->prepare("SELECT * FROM metodos_pago WHERE id_usuario = :id ORDER BY fecha_alta DESC");
$stmt->execute(['id' => $uid]);
$metodosGuardados = array_values(array_filter($stmt->fetchAll(), fn($m) => $m['tipo'] === 'paypal' || caducidadValida($m['caducidad'])));

$mensajeError = leeMensaje('mensaje_error');
$mensajeDireccion = leeMensaje('mensaje_perfil');

function textoDireccion($dir)
{
    return $dir['nombre'] . ' ' . $dir['apellido'] . ' - ' . $dir['calle'] . ', ' . $dir['codigo_postal'] . ' ' . $dir['ciudad'];
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

        <?php if ($mensajeError): ?>
            <div class="mensaje-error"><?php echo e($mensajeError); ?></div>
        <?php endif; ?>
        <?php if ($mensajeDireccion): ?>
            <div class="mensaje-perfil"><?php echo e($mensajeDireccion); ?></div>
        <?php endif; ?>
        <?php if ($hayProblemasStock): ?>
            <div class="mensaje-error">Algún producto de tu carrito no tiene stock suficiente. Ajusta las cantidades antes de continuar.</div>
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
                        <?php foreach ($lineas as $item): ?>
                            <tr>
                                <td>
                                    <div class="producto-checkout">
                                        <img src="<?php echo e(urlImagen($item['imagen'])); ?>" alt="<?php echo e($item['nombre']); ?>">
                                        <span><?php echo e($item['nombre']); ?></span>
                                    </div>
                                </td>
                                <td>
                                    <?php echo (int) $item['cantidad']; ?>
                                    <?php if ($item['cantidad'] > $item['stock']): ?>
                                        <br><small class="stock-agotado">Stock: <?php echo (int) $item['stock']; ?></small>
                                    <?php endif; ?>
                                </td>
                                <td><?php echo formatoPrecio($item['subtotal']); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                <p class="envio-checkout">Envío: <strong>Gratis</strong></p>
                <p class="total" style="font-size: 1.2em; font-weight: bold; margin-top: 20px;">
                    Total a pagar: <?php echo formatoPrecio($total); ?>
                </p>
            </div>

            <!-- Selección de dirección y pago -->
            <div class="seleccion-direccion">
                <form action="php/crea_pedido.php" method="POST" id="form-checkout">
                    <?php echo campoCsrf(); ?>
                    <!-- Sección Envío -->
                    <h3>Dirección de envío</h3>
                    <div class="form-group">
                        <select name="id_direccion" id="select-envio" class="form-control" required onchange="checkNewAddress('envio')">
                            <option value="">Selecciona una dirección de envío...</option>
                            <?php foreach ($direccionesEnvio as $i => $dir): ?>
                                <option value="<?php echo (int) $dir['id_direccion']; ?>" <?php echo $i === 0 ? 'selected' : ''; ?>>
                                    <?php echo e(textoDireccion($dir)); ?>
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
                            <?php foreach ($direccionesFacturacion as $i => $dir): ?>
                                <option value="<?php echo (int) $dir['id_direccion']; ?>" <?php echo $i === 0 ? 'selected' : ''; ?>>
                                    <?php echo e(textoDireccion($dir)); ?>
                                </option>
                            <?php endforeach; ?>
                            <option value="new">+ Añadir nueva dirección de facturación...</option>
                        </select>
                    </div>

                    <!-- Sección Pago -->
                    <h3 style="margin-top: 20px;">Método de pago</h3>
                    <p class="aviso-pago-simulado"><i class="fas fa-circle-info"></i>
                        Pago simulado: no se realiza ningún cargo. Usa la tarjeta <code>4242 4242 4242 4242</code>
                        (o <code>4000 0000 0000 0002</code> para simular un rechazo).</p>

                    <div class="opciones-pago">
                        <?php foreach ($metodosGuardados as $i => $metodo): ?>
                            <label class="opcion-pago">
                                <input type="radio" name="metodo_pago" value="guardado:<?php echo (int) $metodo['id_metodo']; ?>" <?php echo $i === 0 ? 'checked' : ''; ?>>
                                <i class="<?php echo $metodo['tipo'] === 'paypal' ? 'fab fa-paypal' : 'fas fa-credit-card'; ?>"></i>
                                <span><?php echo e(descripcionMetodoPago($metodo)); ?>
                                    <?php if ($metodo['tipo'] === 'tarjeta'): ?><small>Caduca <?php echo e($metodo['caducidad']); ?></small><?php endif; ?>
                                </span>
                            </label>
                        <?php endforeach; ?>

                        <label class="opcion-pago">
                            <input type="radio" name="metodo_pago" value="tarjeta" <?php echo !$metodosGuardados ? 'checked' : ''; ?>>
                            <i class="fas fa-credit-card"></i><span>Nueva tarjeta</span>
                        </label>
                        <div class="campos-pago" data-metodo="tarjeta">
                            <input type="text" name="titular" placeholder="Titular de la tarjeta" class="input-full" autocomplete="cc-name">
                            <input type="text" name="numero_tarjeta" placeholder="Número de tarjeta" class="input-full" inputmode="numeric" autocomplete="cc-number" maxlength="23">
                            <div class="fila-campos">
                                <input type="text" name="caducidad" placeholder="MM/AA" class="input-full" autocomplete="cc-exp" maxlength="5">
                                <input type="text" name="cvv" placeholder="CVV" class="input-full" inputmode="numeric" autocomplete="cc-csc" maxlength="4">
                            </div>
                            <label class="check-guardar"><input type="checkbox" name="guardar_metodo" value="1"> Guardar tarjeta para próximas compras</label>
                        </div>

                        <label class="opcion-pago">
                            <input type="radio" name="metodo_pago" value="paypal">
                            <i class="fab fa-paypal"></i><span>PayPal</span>
                        </label>
                        <div class="campos-pago" data-metodo="paypal">
                            <input type="email" name="email_paypal" placeholder="Correo de tu cuenta PayPal" class="input-full">
                            <label class="check-guardar"><input type="checkbox" name="guardar_metodo" value="1"> Guardar PayPal para próximas compras</label>
                        </div>
                    </div>

                    <div style="margin-top: 30px;">
                        <button type="submit" class="btn btn-confirmar" <?php echo $hayProblemasStock ? 'disabled' : ''; ?>>
                            Pagar <?php echo formatoPrecio($total); ?>
                        </button>
                    </div>
                </form>

                <!-- Formularios Ocultos para añadir dirección -->
                <div id="new-address-forms" style="display: none;">
                    <?php foreach (['envio' => 0, 'facturacion' => 1] as $tipo => $esFacturacion): ?>
                    <div id="form-new-<?php echo $tipo; ?>" class="modal-address" style="display: none;">
                        <div class="modal-content">
                            <span class="close-modal" onclick="closeModal('<?php echo $tipo; ?>')">&times;</span>
                            <h4>Nueva Dirección de <?php echo $esFacturacion ? 'Facturación' : 'Envío'; ?></h4>
                            <form action="php/direcciones_handler.php" method="POST">
                                <?php echo campoCsrf(); ?>
                                <input type="hidden" name="action" value="add">
                                <input type="hidden" name="redirect" value="checkout">
                                <input type="hidden" name="facturacion" value="<?php echo $esFacturacion; ?>">

                                <input type="text" name="nombre" placeholder="Nombre" required class="input-full">
                                <input type="text" name="apellido" placeholder="Apellido" required class="input-full">
                                <input type="text" name="calle" placeholder="Calle" required class="input-full">
                                <input type="text" name="ciudad" placeholder="Ciudad" required class="input-full">
                                <input type="text" name="codigo_postal" placeholder="Código Postal" required class="input-full">
                                <input type="text" name="provincia" placeholder="Provincia" required class="input-full">
                                <input type="text" name="pais" placeholder="País" required class="input-full" value="España">

                                <button type="submit" class="btn">Guardar Dirección</button>
                            </form>
                        </div>
                    </div>
                    <?php endforeach; ?>
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

        // Mostrar solo los campos del método de pago seleccionado
        function actualizaCamposPago() {
            var seleccionado = document.querySelector('input[name="metodo_pago"]:checked');
            document.querySelectorAll('.campos-pago').forEach(function (campos) {
                var visible = seleccionado && campos.dataset.metodo === seleccionado.value;
                campos.style.display = visible ? 'block' : 'none';
                campos.querySelectorAll('input').forEach(function (input) {
                    input.disabled = !visible;
                    if (input.type !== 'checkbox') input.required = visible;
                });
            });
        }
        document.querySelectorAll('input[name="metodo_pago"]').forEach(function (radio) {
            radio.addEventListener('change', actualizaCamposPago);
        });
        actualizaCamposPago();

        // Formato del número de tarjeta (grupos de 4) y de la caducidad (MM/AA)
        var inputNumero = document.querySelector('input[name="numero_tarjeta"]');
        inputNumero.addEventListener('input', function () {
            this.value = this.value.replace(/\D/g, '').slice(0, 19).replace(/(.{4})(?=.)/g, '$1 ');
        });
        var inputCaducidad = document.querySelector('input[name="caducidad"]');
        inputCaducidad.addEventListener('input', function () {
            var digitos = this.value.replace(/\D/g, '').slice(0, 4);
            this.value = digitos.length > 2 ? digitos.slice(0, 2) + '/' + digitos.slice(2) : digitos;
        });

        // Evitar el doble envío del pedido
        document.getElementById('form-checkout').addEventListener('submit', function () {
            var boton = this.querySelector('.btn-confirmar');
            boton.disabled = true;
            boton.textContent = 'Procesando pago...';
        });
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
