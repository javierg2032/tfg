<?php
// Funciones comunes de la tienda: entorno, seguridad, carrito, pedidos y pagos.

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Máximo de unidades de un mismo producto por pedido
const MAX_UNIDADES_PRODUCTO = 5;

// Estados por los que pasa un pedido (clave en BD => texto visible)
const ESTADOS_PEDIDO = [
    'pendiente' => 'Pendiente de pago',
    'pagado' => 'Pagado',
    'enviado' => 'Enviado',
    'entregado' => 'Entregado',
    'cancelado' => 'Cancelado',
];

// Estados desde los que el propio cliente puede cancelar su pedido
const ESTADOS_CANCELABLES = ['pendiente', 'pagado'];

/* ===================== Entorno ===================== */

// Lee un archivo .env sin librerías externas
function cargaEnv($ruta)
{
    $variables = [];
    if (!file_exists($ruta)) return $variables;

    $lineas = file($ruta, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lineas as $linea) {
        $linea = trim($linea);
        if ($linea === '' || $linea[0] === '#' || strpos($linea, '=') === false) continue;
        list($clave, $valor) = explode('=', $linea, 2);
        $variables[trim($clave)] = trim(trim($valor), "\"'");
    }
    return $variables;
}

/* ===================== Salida HTML ===================== */

// Escapa texto para mostrarlo en HTML
function e($texto)
{
    return htmlspecialchars((string) ($texto ?? ''), ENT_QUOTES, 'UTF-8');
}

// Las imágenes se guardan como "/assets/archivo.jpg"; se devuelven relativas a la raíz del proyecto
// para que la web funcione en cualquier carpeta (antes dependía de estar en /tfg).
function urlImagen($ruta)
{
    $ruta = ltrim((string) $ruta, '/');
    return $ruta !== '' ? $ruta : 'assets/placeholder.png';
}

function formatoPrecio($precio)
{
    return number_format((float) $precio, 2, ',', '.') . ' €';
}

function etiquetaEstado($estado)
{
    return ESTADOS_PEDIDO[$estado] ?? ucfirst((string) $estado);
}

/* ===================== Mensajes flash ===================== */

function guardaMensaje($clave, $mensaje)
{
    $_SESSION[$clave] = $mensaje;
}

// Devuelve y borra un mensaje guardado en sesión
function leeMensaje($clave)
{
    $mensaje = $_SESSION[$clave] ?? null;
    unset($_SESSION[$clave]);
    return $mensaje;
}

/* ===================== Redirecciones ===================== */

// URL a la que volver tras procesar un formulario: la página de origen si es de este mismo sitio
function urlRetorno($porDefecto = '../index.php')
{
    $referer = $_SERVER['HTTP_REFERER'] ?? '';
    $hostReferer = parse_url($referer, PHP_URL_HOST);
    if ($referer && $hostReferer === parse_url('http://' . ($_SERVER['HTTP_HOST'] ?? ''), PHP_URL_HOST)) {
        return $referer;
    }
    return $porDefecto;
}

function redirige($url)
{
    header('Location: ' . $url);
    exit;
}

/* ===================== Seguridad ===================== */

function tokenCsrf()
{
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

// Campo oculto para incluir en todos los formularios POST
function campoCsrf()
{
    return '<input type="hidden" name="csrf_token" value="' . e(tokenCsrf()) . '">';
}

function csrfValido()
{
    $token = $_POST['csrf_token'] ?? $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '';
    return is_string($token) && $token !== '' && hash_equals(tokenCsrf(), $token);
}

// Corta la petición si no es POST o el token CSRF no coincide
function exigePostConCsrf($claveMensaje, $redireccion)
{
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        guardaMensaje($claveMensaje, 'Método no permitido.');
        redirige($redireccion);
    }
    if (!csrfValido()) {
        guardaMensaje($claveMensaje, 'La sesión ha caducado. Vuelve a intentarlo.');
        redirige($redireccion);
    }
}

function estaLogueado()
{
    return isset($_SESSION['usuario_id']);
}

function esAdmin()
{
    return !empty($_SESSION['es_admin']) && $_SESSION['es_admin'] === true;
}

function exigeAdmin($redireccion = '../index.php')
{
    if (!esAdmin()) {
        redirige($redireccion);
    }
}

// Guarda una imagen de producto validando que sea realmente una imagen.
// Devuelve la ruta a guardar en BD ("/assets/...") o lanza una excepción.
function subeImagenProducto(array $archivo)
{
    if (($archivo['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
        throw new Exception('Error al subir la imagen.');
    }
    if ($archivo['size'] > 5 * 1024 * 1024) {
        throw new Exception('La imagen no puede superar los 5 MB.');
    }

    $tiposPermitidos = [
        'image/jpeg' => 'jpg',
        'image/png' => 'png',
        'image/webp' => 'webp',
        'image/gif' => 'gif',
    ];
    $tipo = (new finfo(FILEINFO_MIME_TYPE))->file($archivo['tmp_name']);
    if (!isset($tiposPermitidos[$tipo]) || @getimagesize($archivo['tmp_name']) === false) {
        throw new Exception('Formato de imagen no permitido (usa JPG, PNG, WEBP o GIF).');
    }

    // Nombre aleatorio: evita sobrescribir archivos y que se suban scripts con nombre propio
    $nombreArchivo = time() . '_' . bin2hex(random_bytes(5)) . '.' . $tiposPermitidos[$tipo];
    if (!move_uploaded_file($archivo['tmp_name'], __DIR__ . '/../assets/' . $nombreArchivo)) {
        throw new Exception('No se pudo guardar la imagen.');
    }
    return '/assets/' . $nombreArchivo;
}

/* ===================== Carrito ===================== */

// Al iniciar sesión, recupera el carrito guardado en BD y lo combina con el de la sesión
function cargaCarritoGuardado(PDO $pdo, $idUsuario)
{
    $carrito = $_SESSION['carrito'] ?? [];

    $stmt = $pdo->prepare("SELECT c.producto_id, c.cantidad, p.nombre, p.precio, p.imagen, p.stock
        FROM carrito c JOIN productos p ON p.id_producto = c.producto_id
        WHERE c.usuario_id = :uid");
    $stmt->execute(['uid' => $idUsuario]);

    foreach ($stmt->fetchAll() as $fila) {
        $id = (int) $fila['producto_id'];
        $cantidad = max((int) $fila['cantidad'], $carrito[$id]['cantidad'] ?? 0);
        $cantidad = min($cantidad, (int) $fila['stock'], MAX_UNIDADES_PRODUCTO);
        if ($cantidad <= 0) continue;
        $carrito[$id] = [
            'nombre' => $fila['nombre'],
            'precio' => $fila['precio'],
            'imagen' => $fila['imagen'],
            'cantidad' => $cantidad,
        ];
    }
    $_SESSION['carrito'] = $carrito;

    // Guardar en BD lo que hubiera en la sesión antes de iniciar sesión
    $stmt = $pdo->prepare("INSERT INTO carrito (usuario_id, producto_id, cantidad) VALUES (:uid, :pid, :cant)
        ON DUPLICATE KEY UPDATE cantidad = VALUES(cantidad)");
    foreach ($carrito as $id => $item) {
        $stmt->execute(['uid' => $idUsuario, 'pid' => $id, 'cant' => $item['cantidad']]);
    }
}

// Devuelve las líneas del carrito con el precio y stock actuales de la BD (nunca se confía en la sesión).
// Con $bloquear = true bloquea las filas de productos dentro de una transacción.
function lineasCarrito(PDO $pdo, $bloquear = false)
{
    $carrito = $_SESSION['carrito'] ?? [];
    if (!$carrito) return [];

    $ids = array_map('intval', array_keys($carrito));
    $marcadores = implode(',', array_fill(0, count($ids), '?'));
    $sql = "SELECT id_producto, nombre, precio, imagen, stock FROM productos WHERE id_producto IN ($marcadores)"
        . ($bloquear ? ' FOR UPDATE' : '');
    $stmt = $pdo->prepare($sql);
    $stmt->execute($ids);

    $lineas = [];
    foreach ($stmt->fetchAll() as $producto) {
        $id = (int) $producto['id_producto'];
        $cantidad = (int) $carrito[$id]['cantidad'];
        $lineas[$id] = $producto + [
            'cantidad' => $cantidad,
            'subtotal' => $cantidad * (float) $producto['precio'],
        ];
    }
    return $lineas;
}

/* ===================== Pagos (simulados) ===================== */

// Algoritmo de Luhn para validar números de tarjeta
function numeroTarjetaValido($numero)
{
    if (!preg_match('/^\d{13,19}$/', $numero)) return false;
    $suma = 0;
    $doble = false;
    for ($i = strlen($numero) - 1; $i >= 0; $i--) {
        $digito = (int) $numero[$i];
        if ($doble) {
            $digito *= 2;
            if ($digito > 9) $digito -= 9;
        }
        $suma += $digito;
        $doble = !$doble;
    }
    return $suma % 10 === 0;
}

function marcaTarjeta($numero)
{
    if (preg_match('/^4/', $numero)) return 'Visa';
    if (preg_match('/^(5[1-5]|2[2-7])/', $numero)) return 'Mastercard';
    if (preg_match('/^3[47]/', $numero)) return 'American Express';
    return 'Tarjeta';
}

// Caducidad en formato MM/AA, no vencida
function caducidadValida($caducidad)
{
    if (!preg_match('/^(0[1-9]|1[0-2])\/(\d{2})$/', $caducidad, $m)) return false;
    $finDeMes = DateTime::createFromFormat('Y-m-d', '20' . $m[2] . '-' . $m[1] . '-01')->modify('last day of this month');
    return $finDeMes >= new DateTime('today');
}

// Valida los datos de una tarjeta nueva. Devuelve los datos seguros para guardar (nunca el número completo ni el CVV).
function validaTarjeta($titular, $numero, $caducidad, $cvv)
{
    $numero = preg_replace('/[\s-]/', '', (string) $numero);
    $titular = trim((string) $titular);

    if ($titular === '') throw new Exception('Indica el titular de la tarjeta.');
    if (!numeroTarjetaValido($numero)) throw new Exception('El número de tarjeta no es válido.');
    if (!caducidadValida(trim((string) $caducidad))) throw new Exception('La fecha de caducidad no es válida o la tarjeta está caducada.');
    if (!preg_match('/^\d{3,4}$/', (string) $cvv)) throw new Exception('El CVV no es válido.');

    return [
        'titular' => $titular,
        'marca' => marcaTarjeta($numero),
        'ultimos4' => substr($numero, -4),
        'caducidad' => trim($caducidad),
        // Tarjeta de prueba que simula un rechazo del banco
        'rechazada' => $numero === '4000000000000002',
    ];
}

// Texto que identifica un método de pago guardado
function descripcionMetodoPago(array $metodo)
{
    if ($metodo['tipo'] === 'paypal') {
        return 'PayPal (' . $metodo['email_paypal'] . ')';
    }
    return $metodo['marca'] . ' •••• ' . $metodo['ultimos4'];
}

/* ===================== Pedidos ===================== */

// Cambia el estado de un pedido. Al cancelarlo se devuelve el stock de sus productos.
// Debe llamarse dentro de una transacción.
function cambiaEstadoPedido(PDO $pdo, $idPedido, $estadoActual, $estadoNuevo)
{
    if ($estadoActual === $estadoNuevo) return;

    if ($estadoNuevo === 'cancelado') {
        $stmt = $pdo->prepare("UPDATE productos p JOIN detalles_pedido dp ON dp.id_producto = p.id_producto
            SET p.stock = p.stock + dp.cantidad WHERE dp.id_pedido = :id");
        $stmt->execute(['id' => $idPedido]);
    }

    $stmt = $pdo->prepare("UPDATE pedidos SET estado = :estado WHERE id_pedido = :id");
    $stmt->execute(['estado' => $estadoNuevo, 'id' => $idPedido]);
}

/* ===================== Productos ===================== */

// Lee y valida los campos comunes del formulario de producto (alta y edición)
function datosProductoFormulario()
{
    $nombre = trim($_POST['nombre'] ?? '');
    $precio = filter_var($_POST['precio'] ?? '', FILTER_VALIDATE_FLOAT);
    $stock = filter_var($_POST['stock'] ?? '', FILTER_VALIDATE_INT);
    $idCategoria = (int) ($_POST['id_categoria'] ?? 0);
    $descripcion = trim($_POST['descripcion'] ?? '');

    if ($nombre === '' || mb_strlen($nombre) > 150) throw new Exception('El nombre es obligatorio (máx. 150 caracteres).');
    if ($precio === false || $precio < 0) throw new Exception('El precio no es válido.');
    if ($stock === false || $stock < 0) throw new Exception('El stock no es válido.');
    if (!$idCategoria) throw new Exception('Selecciona una categoría.');

    return [
        'nombre' => $nombre,
        'precio' => round($precio, 2),
        'descripcion' => $descripcion !== '' ? $descripcion : null,
        'stock' => $stock,
        'id_categoria' => $idCategoria,
    ];
}
