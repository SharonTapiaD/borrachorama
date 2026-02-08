<?php
session_start();
include __DIR__ . "/config/conexion.php";

// 1. Validar sesión
if (!isset($_SESSION['usuario_id'])) {
    echo "Error: No hay sesión.";
    exit;
}
$usuario_id = $_SESSION['usuario_id'];

// 2. Recibir datos (usamos ?? para asignar null si vienen vacíos)
// Usamos trim() para quitar espacios y verificar si realmente hay texto
$telefono      = (isset($_POST['telefono']) && trim($_POST['telefono']) !== '') ? trim($_POST['telefono']) : null;
$direccion     = (isset($_POST['direccion']) && trim($_POST['direccion']) !== '') ? trim($_POST['direccion']) : null;
$forma_pago_id = (isset($_POST['forma_pago_id']) && $_POST['forma_pago_id'] !== '') ? intval($_POST['forma_pago_id']) : null;

// 3. Manejo de Foto
$fotoNombre = null;
if (isset($_FILES['foto']) && $_FILES['foto']['error'] === 0) {
    $carpeta = "../uploads/";
    if (!is_dir($carpeta)) mkdir($carpeta, 0777, true);
    
    $ext = pathinfo($_FILES['foto']['name'], PATHINFO_EXTENSION);
    $fotoNombre = "perfil_" . $usuario_id . "_" . time() . "." . $ext;
    move_uploaded_file($_FILES['foto']['tmp_name'], $carpeta . $fotoNombre);
}

// 4. Verificar si existe
$sqlCheck = "SELECT id FROM perfiles WHERE usuario_id = ?";
$stmtCheck = $conn->prepare($sqlCheck);
$stmtCheck->bind_param("i", $usuario_id);
$stmtCheck->execute();
$existe = $stmtCheck->get_result()->num_rows > 0;

if ($existe) {
    // ACTUALIZAR
    if ($fotoNombre) {
        $sql = "UPDATE perfiles SET telefono=?, direccion=?, forma_pago_id=?, foto=? WHERE usuario_id=?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssisi", $telefono, $direccion, $forma_pago_id, $fotoNombre, $usuario_id);
    } else {
        $sql = "UPDATE perfiles SET telefono=?, direccion=?, forma_pago_id=? WHERE usuario_id=?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssii", $telefono, $direccion, $forma_pago_id, $usuario_id);
    }
} else {
    // INSERTAR
    $sql = "INSERT INTO perfiles (usuario_id, telefono, direccion, forma_pago_id, foto) VALUES (?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("issis", $usuario_id, $telefono, $direccion, $forma_pago_id, $fotoNombre);
}

if ($stmt->execute()) {
    echo "Perfil actualizado correctamente";
} else {
    echo "Error: " . $stmt->error;
}