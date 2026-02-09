<?php
session_start();

/*
🔐 OPCIONAL (recomendado luego)
Si solo el admin puede crear gerentes, descomenta esto:

if (!isset($_SESSION['rol']) || $_SESSION['rol'] !== 'admin') {
    header("Location: /inicio.html");
    exit;
}
*/

require_once __DIR__ . "/config/conexion.php";

$msg = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $nombre   = trim($_POST['nombre'] ?? '');
    $correo   = trim($_POST['correo'] ?? '');
    $password = trim($_POST['password'] ?? '');

    if ($nombre === "" || $correo === "" || $password === "") {
        $msg = "❌ Todos los campos son obligatorios";
    } else {

        // Verificar si el correo ya existe
        $sqlCheck = "SELECT id FROM usuarios WHERE correo = ?";
        $stmtCheck = $conn->prepare($sqlCheck);
        $stmtCheck->bind_param("s", $correo);
        $stmtCheck->execute();
        $res = $stmtCheck->get_result();

        if ($res->num_rows > 0) {
            $msg = "❌ Ese correo ya existe";
        } else {

            // Encriptar contraseña
            $hash = password_hash($password, PASSWORD_DEFAULT);

            // Insertar GERENTE
            $sql = "INSERT INTO usuarios (nombre, correo, password, rol, estatus)
                    VALUES (?, ?, ?, 'gerente', 'activo')";

            $stmt = $conn->prepare($sql);
            $stmt->bind_param("sss", $nombre, $correo, $hash);

            if ($stmt->execute()) {
                $msg = "✅ Gerente creado correctamente";
            } else {
                $msg = "❌ Error al crear gerente";
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Crear Gerente</title>
</head>
<body>

<h2>Crear Gerente</h2>

<p style="color:blue;">
    <?php echo htmlspecialchars($msg); ?>
</p>

<form method="POST">

    <label>Nombre</label><br>
    <input type="text" name="nombre" required><br><br>

    <label>Correo</label><br>
    <input type="email" name="correo" required><br><br>

    <label>Contraseña</label><br>
    <input type="password" name="password" required><br><br>

    <button type="submit">Crear Gerente</button>

</form>

</body>
</html>
