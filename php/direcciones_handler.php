<?php
session_start();
require 'config.php';

if (!isset($_SESSION['usuario_id'])) {
    header('Location: ../perfil.php');
    exit;
}

$action = $_POST['action'] ?? '';
$uid = (int) $_SESSION['usuario_id'];

// Detectar si la columna 'facturacion' existe (compatibilidad con esquema actualizado)
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

try {
    if ($action === 'add') {
        $nombre = trim($_POST['nombre'] ?? '');
        $apellido = trim($_POST['apellido'] ?? '');
        $calle = trim($_POST['calle'] ?? '');
        $ciudad = trim($_POST['ciudad'] ?? '');
        $codigo_postal = trim($_POST['codigo_postal'] ?? '');
        $provincia = trim($_POST['provincia'] ?? '');
        $pais = trim($_POST['pais'] ?? '');
        $id_tipo = (int) ($_POST['id_tipo'] ?? 1);
        $facturacion = $use_facturacion_column ? (isset($_POST['facturacion']) ? 1 : 0) : null;

        if (!$nombre || !$apellido || !$calle) {
            throw new Exception('Campos obligatorios faltantes');
        }

        // Insertar en direcciones (incluir facturacion si existe la columna)
        if ($use_facturacion_column) {
            $stmt = $pdo->prepare("INSERT INTO direcciones (nombre, apellido, calle, ciudad, codigo_postal, provincia, pais, facturacion) VALUES (:nombre, :apellido, :calle, :ciudad, :codigo_postal, :provincia, :pais, :facturacion)");
            $stmt->execute([
                'nombre' => $nombre,
                'apellido' => $apellido,
                'calle' => $calle,
                'ciudad' => $ciudad,
                'codigo_postal' => $codigo_postal,
                'provincia' => $provincia,
                'pais' => $pais,
                'facturacion' => $facturacion
            ]);
        } else {
            $stmt = $pdo->prepare("INSERT INTO direcciones (nombre, apellido, calle, ciudad, codigo_postal, provincia, pais) VALUES (:nombre, :apellido, :calle, :ciudad, :codigo_postal, :provincia, :pais)");
            $stmt->execute([
                'nombre' => $nombre,
                'apellido' => $apellido,
                'calle' => $calle,
                'ciudad' => $ciudad,
                'codigo_postal' => $codigo_postal,
                'provincia' => $provincia,
                'pais' => $pais
            ]);
        }

        $id_direccion = $pdo->lastInsertId();

        // Vincular al usuario
        $stmt = $pdo->prepare("INSERT INTO usuarios_direcciones (id_usuario, id_direccion, id_tipo) VALUES (:id_usuario, :id_direccion, :id_tipo)");
        $stmt->execute(['id_usuario' => $uid, 'id_direccion' => $id_direccion, 'id_tipo' => $id_tipo]);
        // id del vínculo recién creado
        $id_usuario_direccion = (int) $pdo->lastInsertId();

    } elseif ($action === 'edit') {
        $id_usuario_direccion = (int) ($_POST['id_usuario_direccion'] ?? 0);
        $id_direccion = (int) ($_POST['id_direccion'] ?? 0);
        $nombre = trim($_POST['nombre'] ?? '');
        $apellido = trim($_POST['apellido'] ?? '');
        $calle = trim($_POST['calle'] ?? '');
        $ciudad = trim($_POST['ciudad'] ?? '');
        $codigo_postal = trim($_POST['codigo_postal'] ?? '');
        $provincia = trim($_POST['provincia'] ?? '');
        $pais = trim($_POST['pais'] ?? '');
        $id_tipo = (int) ($_POST['id_tipo'] ?? 1);
        $facturacion = $use_facturacion_column ? (isset($_POST['facturacion']) ? 1 : 0) : null;

        if (!$id_usuario_direccion || !$id_direccion) {
            throw new Exception('Identificadores no válidos');
        }

        // Comprobar que la dirección pertenece al usuario
        $stmt = $pdo->prepare("SELECT id_usuario FROM usuarios_direcciones WHERE id_usuario_direccion = :id_ud");
        $stmt->execute(['id_ud' => $id_usuario_direccion]);
        $ud = $stmt->fetch();
        if (!$ud || (int) $ud['id_usuario'] !== $uid) {
            throw new Exception('No autorizado');
        }

        // Actualizar tabla direcciones (incluir facturacion si aplica)
        if ($use_facturacion_column) {
            $stmt = $pdo->prepare("UPDATE direcciones SET nombre = :nombre, apellido = :apellido, calle = :calle, ciudad = :ciudad, codigo_postal = :codigo_postal, provincia = :provincia, pais = :pais, facturacion = :facturacion WHERE id_direccion = :id_direccion");
            $stmt->execute([
                'nombre' => $nombre,
                'apellido' => $apellido,
                'calle' => $calle,
                'ciudad' => $ciudad,
                'codigo_postal' => $codigo_postal,
                'provincia' => $provincia,
                'pais' => $pais,
                'facturacion' => $facturacion,
                'id_direccion' => $id_direccion
            ]);
        } else {
            $stmt = $pdo->prepare("UPDATE direcciones SET nombre = :nombre, apellido = :apellido, calle = :calle, ciudad = :ciudad, codigo_postal = :codigo_postal, provincia = :provincia, pais = :pais WHERE id_direccion = :id_direccion");
            $stmt->execute([
                'nombre' => $nombre,
                'apellido' => $apellido,
                'calle' => $calle,
                'ciudad' => $ciudad,
                'codigo_postal' => $codigo_postal,
                'provincia' => $provincia,
                'pais' => $pais,
                'id_direccion' => $id_direccion
            ]);
        }

        // Actualizar tipo en usuarios_direcciones
        $stmt = $pdo->prepare("UPDATE usuarios_direcciones SET id_tipo = :id_tipo WHERE id_usuario_direccion = :id_ud");
        $stmt->execute(['id_tipo' => $id_tipo, 'id_ud' => $id_usuario_direccion]);

    } elseif ($action === 'delete') {
        $id_usuario_direccion = (int) ($_POST['id_usuario_direccion'] ?? 0);
        if (!$id_usuario_direccion)
            throw new Exception('Identificador inválido');

        // Comprobar propietario
        $stmt = $pdo->prepare("SELECT id_usuario, id_direccion FROM usuarios_direcciones WHERE id_usuario_direccion = :id_ud");
        $stmt->execute(['id_ud' => $id_usuario_direccion]);
        $ud = $stmt->fetch();
        if (!$ud || (int) $ud['id_usuario'] !== $uid)
            throw new Exception('No autorizado');

        // Eliminar el vínculo; la dirección en sí se mantiene (o se elimina si quieres)
        $stmt = $pdo->prepare("DELETE FROM usuarios_direcciones WHERE id_usuario_direccion = :id_ud");
        $stmt->execute(['id_ud' => $id_usuario_direccion]);
    }
} catch (Exception $e) {
    // Guardar mensaje en sesión y redirigir a perfil
    $_SESSION['mensaje_perfil'] = $e->getMessage();
    header('Location: ../perfil.php');
    exit;
}
// Si todo fue bien y no hubo excepciones
// Si todo fue bien: guardar mensaje y redirigir
$_SESSION['mensaje_perfil'] = 'Operación realizada con éxito';
header('Location: ../perfil.php');
exit;