<?php
$host = "localhost";
$usuario = "root";
$password = "";
$bd = "borrachorama";

$conn = new mysqli($host, $usuario, $password, $bd);

if ($conn->connect_error) {
    die("Error de conexión: " . $conn->connect_error);
}

$conn->set_charset("utf8");
?>
