<?php
header("Content-Type: application/json");
include __DIR__ . "/config/conexion.php";

/* =========================
   RECIBIR DATOS
========================= */

$nombre   = trim($_POST['nombre'] ?? '');
$correo   = trim($_POST['correo'] ?? '');
$password = trim($_POST['password'] ?? '');

/* =========================
   VALIDACIONES
========================= */

if($nombre == '' || $correo == '' || $password == ''){
    echo json_encode([
        "status" => "error",
        "msg" => "Todos los campos son obligatorios"
    ]);
    exit;
}

/* =========================
   VERIFICAR CORREO EXISTENTE
========================= */

$sql = "SELECT id FROM usuarios WHERE correo = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $correo);
$stmt->execute();
$result = $stmt->get_result();

if($result->num_rows > 0){
    echo json_encode([
        "status" => "error",
        "msg" => "El correo ya está registrado"
    ]);
    exit;
}

/* =========================
   ENCRIPTAR PASSWORD
========================= */

$hash = password_hash($password, PASSWORD_DEFAULT);

/* =========================
   INSERTAR USUARIO
========================= */

$sql = "INSERT INTO usuarios (nombre, correo, password, rol, estatus)
        VALUES (?, ?, ?, 'usuario', 'activo')";

$stmt = $conn->prepare($sql);
$stmt->bind_param("sss", $nombre, $correo, $hash);

if(!$stmt->execute()){
    echo json_encode([
        "status" => "error",
        "msg" => "Error al registrar usuario"
    ]);
    exit;
}

$usuario_id = $stmt->insert_id;

/* =========================
   CREAR PERFIL VACÍO AUTOMÁTICO
========================= */

$sqlPerfil = "INSERT INTO perfiles (usuario_id) VALUES (?)";
$stmtPerfil = $conn->prepare($sqlPerfil);
$stmtPerfil->bind_param("i", $usuario_id);
$stmtPerfil->execute();

/* =========================
   RESPUESTA OK
========================= */

echo json_encode([
    "status" => "ok",
    "msg" => "Usuario registrado correctamente"
]);
?>
