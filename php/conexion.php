<?php
$host = "localhost";
$usuario = "root";     // usuario por defecto de XAMPP
$password = "";         // contraseña vacía por defecto
$base_de_datos = "ryujin";

$conn = new mysqli($host, $usuario, $password, $base_de_datos);

if ($conn->connect_error) {
    die("Conexión fallida: " . $conn->connect_error);
}
?>