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

        // Verificar si ya existe el correo
        $sqlCheck = "SELECT id FROM usuarios WHERE correo = ?";
        $stmtCheck = $conn->prepare($sqlCheck);
        $stmtCheck->bind_param("s", $correo);
        $stmtCheck->execute();
        $res = $stmtCheck->get_result();

        if ($res->num_rows > 0) {
            $msg = "❌ Ese correo ya está registrado";
        } else {

            $hash = password_hash($password, PASSWORD_DEFAULT);

            $sql = "INSERT INTO usuarios (nombre, correo, password, rol, estatus)
                    VALUES (?, ?, ?, 'ventas', 'activo')";

            $stmt = $conn->prepare($sql);
            $stmt->bind_param("sss", $nombre, $correo, $hash);

            if ($stmt->execute()) {
                $msg = "✅ Usuario de ventas creado correctamente";
            } else {
                $msg = "❌ Error al crear usuario";
            }
        }
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Crear Usuario Ventas</title>
</head>
<body>

<h2>Registrar Usuario Ventas</h2>

<p><?php echo htmlspecialchars($msg); ?></p>

<form method="POST">
    <label>Nombre</label><br>
    <input type="text" name="nombre" required><br><br>

    <label>Correo</label><br>
    <input type="email" name="correo" required><br><br>

    <label>Contraseña</label><br>
    <input type="password" name="password" required><br><br>

    <button type="submit">Crear Usuario</button>
</form>

</body>
</html>