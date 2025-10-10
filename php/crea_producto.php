<?php
require_once "config.php";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre = $_POST['nombre'];
    $precio = $_POST['precio'];
    $descripcion = !empty($_POST['descripcion']) ? $_POST['descripcion'] : null;
    $stock = $_POST['stock'];
    $id_categoria = $_POST['id_categoria'];

    // Validar que se haya subido una imagen
    if (!empty($_FILES['imagen']['name'])) {
        $nombreArchivo = basename($_FILES['imagen']['name']);
        $rutaDestino = "../assets/" . $nombreArchivo;

        // Mover imagen
        if (move_uploaded_file($_FILES['imagen']['tmp_name'], $rutaDestino)) {
            $ruta_guardada = "/assets/" . $nombreArchivo;

            // Insertar el producto en la base de datos
            $sql = "INSERT INTO productos (nombre, precio, descripcion, stock, imagen, id_categoria)
                    VALUES (?, ?, ?, ?, ?, ?)";
            $stmt = $pdo->prepare($sql);

            try {
                $stmt->execute([$nombre, $precio, $descripcion, $stock, $ruta_guardada, $id_categoria]);
                echo "<script>window.location.href='../admin.php';</script>";
            } catch (PDOException $e) {
                echo "Error al insertar el producto: " . $e->getMessage();
            }
        } else {
            echo "Error al subir la imagen.";
        }
    } else {
        echo "Debe seleccionar una imagen para el producto.";
    }
} else {
    echo "Método no permitido.";
}
?>
