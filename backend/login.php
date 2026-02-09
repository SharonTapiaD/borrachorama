<?php
session_start();
session_unset();
session_destroy();
session_start();

header("Content-Type: application/json");
include __DIR__ . "/config/conexion.php";

$correo   = trim($_POST['correo'] ?? '');
$password = trim($_POST['password'] ?? '');

if ($correo == '' || $password == '') {
    echo json_encode([
        "status" => "error",
        "msg" => "Todos los campos son obligatorios"
    ]);
    exit;
}

$sql = "SELECT * FROM usuarios WHERE correo = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $correo);
$stmt->execute();

$result = $stmt->get_result();

if ($result->num_rows == 1) {
    $user = $result->fetch_assoc();
    
    if (password_verify($password, $user['password'])) {
        session_regenerate_id(true);
        $_SESSION['usuario_id'] = $user['id'];
        $_SESSION['nombre'] = $user['nombre'];
        $_SESSION['rol']    = $user['rol'];

        if ($user['rol'] === 'admin') {
            echo json_encode([
                "status" => "ok",
                "msg" => "Bienvenido, " . $user['nombre'],
                "rol" => "admin",
                "nombre" => $user['nombre'],
                "usuario_id" => $user['id'],
                "redirect" => "Administrador.html"
            ]);
        } elseif ($user['rol'] === 'gerente') {
            echo json_encode([
                "status" => "ok",
                "msg" => "Bienvenido, " . $user['nombre'],
                "rol" => "gerente",
                "nombre" => $user['nombre'],
                "usuario_id" => $user['id'],
                "redirect" => "GerenciaPanel.html"
            ]);
        } else {
            echo json_encode([
                "status" => "ok",
                "msg" => "Bienvenido, " . $user['nombre'],
                "rol" => "usuario",
                "nombre" => $user['nombre'],
                "usuario_id" => $user['id'],
                "redirect" => "User.html"
            ]);
        }
        exit;
    } else {
        echo json_encode([
            "status" => "error",
            "msg" => "Credenciales incorrectas"
        ]);
        exit;
    }
} else {
    echo json_encode([
        "status" => "error",
        "msg" => "Credenciales incorrectas"
    ]);
    exit;
}
