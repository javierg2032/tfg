<?php
session_start();
require_once 'config.php';

if (!estaLogueado()) {
    redirige('../perfil.php');
}

// Solo se permite volver al perfil o al checkout (evita redirecciones a sitios externos)
$redirect = ($_POST['redirect'] ?? '') === 'checkout' ? '../checkout.php' : '../perfil.php';

exigePostConCsrf('mensaje_perfil', $redirect);

$action = $_POST['action'] ?? '';
$uid = (int) $_SESSION['usuario_id'];

// Devuelve el vínculo usuario-dirección si pertenece al usuario actual
function vinculoDelUsuario(PDO $pdo, $idUsuarioDireccion, $uid)
{
    $stmt = $pdo->prepare("SELECT id_usuario, id_direccion FROM usuarios_direcciones WHERE id_usuario_direccion = :id_ud");
    $stmt->execute(['id_ud' => $idUsuarioDireccion]);
    $ud = $stmt->fetch();
    if (!$ud || (int) $ud['id_usuario'] !== $uid) {
        throw new Exception('No autorizado');
    }
    return $ud;
}

function datosDireccion()
{
    $campos = ['nombre', 'apellido', 'calle', 'ciudad', 'codigo_postal', 'provincia', 'pais'];
    $datos = [];
    foreach ($campos as $campo) {
        $datos[$campo] = mb_substr(trim($_POST[$campo] ?? ''), 0, 255);
        if ($datos[$campo] === '') {
            throw new Exception('Todos los campos de la dirección son obligatorios.');
        }
    }
    $datos['facturacion'] = !empty($_POST['facturacion']) ? 1 : 0;
    return $datos;
}

try {
    if ($action === 'add') {
        $datos = datosDireccion();

        $stmt = $pdo->prepare("INSERT INTO direcciones (nombre, apellido, calle, ciudad, codigo_postal, provincia, pais, facturacion) VALUES (:nombre, :apellido, :calle, :ciudad, :codigo_postal, :provincia, :pais, :facturacion)");
        $stmt->execute($datos);
        $id_direccion = $pdo->lastInsertId();

        // Vincular al usuario
        $stmt = $pdo->prepare("INSERT INTO usuarios_direcciones (id_usuario, id_direccion) VALUES (:id_usuario, :id_direccion)");
        $stmt->execute(['id_usuario' => $uid, 'id_direccion' => $id_direccion]);

    } elseif ($action === 'edit') {
        // El id de la dirección se obtiene del vínculo del usuario, nunca del formulario,
        // para que no se pueda editar la dirección de otra persona
        $ud = vinculoDelUsuario($pdo, (int) ($_POST['id_usuario_direccion'] ?? 0), $uid);
        $datos = datosDireccion();
        $datos['id_direccion'] = $ud['id_direccion'];

        $stmt = $pdo->prepare("UPDATE direcciones SET nombre = :nombre, apellido = :apellido, calle = :calle, ciudad = :ciudad, codigo_postal = :codigo_postal, provincia = :provincia, pais = :pais, facturacion = :facturacion WHERE id_direccion = :id_direccion");
        $stmt->execute($datos);

    } elseif ($action === 'delete') {
        $id_usuario_direccion = (int) ($_POST['id_usuario_direccion'] ?? 0);
        vinculoDelUsuario($pdo, $id_usuario_direccion, $uid);

        // Eliminar el vínculo; la dirección se conserva porque puede estar en pedidos anteriores
        $stmt = $pdo->prepare("DELETE FROM usuarios_direcciones WHERE id_usuario_direccion = :id_ud");
        $stmt->execute(['id_ud' => $id_usuario_direccion]);
    } else {
        throw new Exception('Acción no válida');
    }
} catch (Exception $e) {
    guardaMensaje('mensaje_perfil', $e instanceof PDOException ? 'No se pudo guardar la dirección.' : $e->getMessage());
    redirige($redirect);
}

// Si todo fue bien
guardaMensaje('mensaje_perfil', 'Operación realizada con éxito');
redirige($redirect);
