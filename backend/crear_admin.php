<?php
include __DIR__ . "/config/conexion.php";

$msg = "";

if($_SERVER["REQUEST_METHOD"] == "POST"){

    $nombre = trim($_POST['nombre']);
    $correo = trim($_POST['correo']);
    $password = trim($_POST['password']);

    if($nombre == "" || $correo == "" || $password == ""){
        $msg = "Todos los campos son obligatorios";
    }
    else {

        /* Verificar si ya existe */
        $sqlCheck = "SELECT id FROM usuarios WHERE correo = ?";
        $stmtCheck = $conn->prepare($sqlCheck);
        $stmtCheck->bind_param("s", $correo);
        $stmtCheck->execute();
        $res = $stmtCheck->get_result();

        if($res->num_rows > 0){
            $msg = "Ese correo ya existe";
        }
        else {

            /* Encriptar */
            $hash = password_hash($password, PASSWORD_DEFAULT);

            /* Insertar admin */
            $sql = "INSERT INTO usuarios (nombre, correo, password, rol, estatus)
                    VALUES (?, ?, ?, 'admin', 'activo')";

            $stmt = $conn->prepare($sql);
            $stmt->bind_param("sss", $nombre, $correo, $hash);

            if($stmt->execute()){
                $msg = "Admin creado correctamente ✅";
            } else {
                $msg = "Error al crear admin ❌";
            }
        }
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Crear Admin</title>
</head>
<body>

<h2>Crear Administrador</h2>

<p style="color:blue;"><?php echo $msg; ?></p>

<form method="POST">

    <label>Nombre</label><br>
    <input type="text" name="nombre"><br><br>

    <label>Correo</label><br>
    <input type="email" name="correo"><br><br>

    <label>Contraseña</label><br>
    <input type="password" name="password"><br><br>

    <button type="submit">Crear Admin</button>

</form>

</body>
</html>