<?php
/**
 * Script para crear un gerente de prueba
 * 
 * Para usar este script:
 * 1. Accede a: http://localhost/xampp10/htdocs/borrachorama/borrachorama/backend/crear_gerente_test.php
 * 2. Se creará un usuario con rol 'gerente'
 * 
 * Credenciales de prueba:
 * Email: gerente@borrachorama.com
 * Contraseña: gerente123
 */

session_start();
include __DIR__ . "/config/conexion.php";

// Verificar conexión
if (!$conn) {
    die("❌ Error de conexión: " . $conn->connect_error);
}

try {
    // Datos del gerente de prueba
    $nombre = "Pedro García";
    $correo = "gerente@borrachorama.com";
    $password = password_hash("gerente123", PASSWORD_DEFAULT);
    $rol = "gerente";

    // Verificar si el correo ya existe
    $sql_check = "SELECT * FROM usuarios WHERE correo = ?";
    $stmt_check = $conn->prepare($sql_check);
    $stmt_check->bind_param("s", $correo);
    $stmt_check->execute();
    $result_check = $stmt_check->get_result();

    if ($result_check->num_rows > 0) {
        echo "⚠️ El correo ya existe en la base de datos";
        exit;
    }

    // Insertar el nuevo gerente
    $sql = "INSERT INTO usuarios (nombre, correo, password, rol) VALUES (?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    
    if (!$stmt) {
        echo "❌ Error en la preparación: " . $conn->error;
        exit;
    }

    $stmt->bind_param("ssss", $nombre, $correo, $password, $rol);

    if ($stmt->execute()) {
        echo "✅ Gerente de prueba creado exitosamente!<br><br>";
        echo "Credenciales de acceso:<br>";
        echo "📧 Email: " . $correo . "<br>";
        echo "🔐 Contraseña: gerente123<br>";
        echo "👤 Nombre: " . $nombre . "<br>";
        echo "📋 Rol: " . $rol . "<br><br>";
        echo "<a href='/Panel.html'>Volver al login</a>";
    } else {
        echo "❌ Error al crear el gerente: " . $stmt->error;
    }

    $stmt->close();
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage();
}

$conn->close();
?>
