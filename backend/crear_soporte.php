<?php
session_start();
require_once __DIR__ . "/config/conexion.php";

$msg = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $nombre   = trim($_POST['nombre'] ?? '');
    $correo   = trim($_POST['correo'] ?? '');
    $password = trim($_POST['password'] ?? '');

    if ($nombre === "" || $correo === "" || $password === "") {
        $msg = "❌ Todos los campos son obligatorios";
    } else {
        $sqlCheck = "SELECT id FROM usuarios WHERE correo = ?";
        $stmtCheck = $conn->prepare($sqlCheck);
        $stmtCheck->bind_param("s", $correo);
        $stmtCheck->execute();
        $res = $stmtCheck->get_result();

        if ($res->num_rows > 0) {
            $msg = "❌ Ese correo ya está registrado";
        } else {
            $hash = password_hash($password, PASSWORD_DEFAULT);
            // Se asigna el rol 'soporte'
            $sql = "INSERT INTO usuarios (nombre, correo, password, rol, estatus) VALUES (?, ?, ?, 'soporte', 'activo')";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("sss", $nombre, $correo, $hash);

            if ($stmt->execute()) {
                $msg = "✅ Usuario de Soporte Técnico creado correctamente";
            } else {
                $msg = "❌ Error al crear usuario";
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Crear Usuario Soporte</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light p-5">
    <div class="card mx-auto" style="max-width: 400px;">
        <div class="card-body">
            <h2 class="text-warning">Crear Perfil Soporte</h2>
            <p><?php echo htmlspecialchars($msg); ?></p>
            <form method="POST">
                <div class="mb-3"><label>Nombre</label><input type="text" name="nombre" class="form-control" required></div>
                <div class="mb-3"><label>Correo</label><input type="email" name="correo" class="form-control" required></div>
                <div class="mb-3"><label>Contraseña</label><input type="password" name="password" class="form-control" required></div>
                <button type="submit" class="btn btn-warning w-100">Registrar</button>
            </form>
        </div>
    </div>
</body>
</html>