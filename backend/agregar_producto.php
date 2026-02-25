<?php

session_start();
header("Content-Type: application/json");

require __DIR__ . "/config/conexion.php";


// Recibir datos del formulario
$nombre       = trim($_POST['nombre'] ?? '');
$descripcion  = trim($_POST['descripcion'] ?? '');
$categoria_id = intval($_POST['categoria_id'] ?? 0);
$precio       = floatval($_POST['precio'] ?? 0);
$stock        = intval($_POST['stock'] ?? 0);
$estatus      = intval($_POST['estatus'] ?? 1);

// Validar campos obligatorios
if ($nombre === '' || $descripcion === '' || $categoria_id === 0 || $precio <= 0) {
    echo json_encode([
        "status" => "error",
        "msg" => "Todos los campos obligatorios deben completarse"
    ]);
    exit;
}

// Manejo de imagen
$imagenRuta = null;
if (isset($_FILES['imagen']) && $_FILES['imagen']['error'] === UPLOAD_ERR_OK) {
    $ext = strtolower(pathinfo($_FILES['imagen']['name'], PATHINFO_EXTENSION));
    $permitidas = ['jpg','jpeg','png','webp','avif'];

    if (!in_array($ext, $permitidas)) {
        echo json_encode([
            "status" => "error",
            "msg" => "Formato de imagen no permitido"
        ]);
        exit;
    }

    // Nombre único para la imagen
    $nuevoNombre = uniqid("prod_", true) . "." . $ext;
    $destino = __DIR__ . "/../imagenes/" . $nuevoNombre; // ajusta ruta según tu estructura

    if (move_uploaded_file($_FILES['imagen']['tmp_name'], $destino)) {
        $imagenRuta = "imagenes/" . $nuevoNombre;
    } else {
        echo json_encode([
            "status" => "error",
            "msg" => "Error al guardar la imagen"
        ]);
        exit;
    }
} else {
    echo json_encode([
        "status" => "error",
        "msg" => "Debe subir una imagen"
    ]);
    exit;
}

// Insertar en la BD
$sql = "INSERT INTO productos (categoria_id, nombre, descripcion, imagen, precio, stock, estatus)
        VALUES (?, ?, ?, ?, ?, ?, ?)";
$stmt = $conn->prepare($sql);
$stmt->bind_param("isssdii", $categoria_id, $nombre, $descripcion, $imagenRuta, $precio, $stock, $estatus);

if ($stmt->execute()) {
    echo json_encode([
        "status" => "ok",
        "msg" => "Producto registrado correctamente",
        "producto_id" => $stmt->insert_id,
        "imagen" => $imagenRuta
    ]);
} else {
    echo json_encode([
        "status" => "error",
        "msg" => "Error al registrar producto: " . $conn->error
    ]);
}
