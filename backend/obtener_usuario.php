<?php
session_start();
header("Content-Type: application/json");

require __DIR__ . "/config/conexion.php";

// Verificar sesión
if (!isset($_SESSION['usuario_id'])) {
    echo json_encode([
        "status" => "error",
        "msg" => "No autenticado"
    ]);
    exit;
}

$usuario_id = $_SESSION['usuario_id'];

$sql = "
SELECT 
    u.nombre,
    u.correo,
    u.rol,
    p.telefono,
    p.direccion,
    p.foto
FROM usuarios u
LEFT JOIN perfiles p ON u.id = p.usuario_id
WHERE u.id = ?
";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $usuario_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 1) {
    $user = $result->fetch_assoc();

    echo json_encode([
        "status"    => "ok",
        "nombre"    => $user['nombre'],
        "correo"    => $user['correo'],
        "rol"       => $user['rol'],
        "telefono"  => $user['telefono'],
        "direccion" => $user['direccion'],
        "foto"      => $user['foto'] 
    ]);
} else {
    echo json_encode([
        "status" => "error",
        "msg" => "Usuario no encontrado"
    ]);
}