<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

// Solo permitir POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Método no permitido']);
    exit;
}

// Obtener datos del POST
$input = json_decode(file_get_contents('php://input'), true);

if (!$input) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Datos inválidos']);
    exit;
}

// Validar datos requeridos
$required = ['to', 'subject', 'body', 'folio'];
foreach ($required as $field) {
    if (!isset($input[$field]) || empty($input[$field])) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => "Campo requerido faltante: $field"]);
        exit;
    }
}

$to = $input['to'];
$subject = $input['subject'];
$body = $input['body'];
$folio = $input['folio'];

// Configurar headers del email
$headers = [
    'MIME-Version: 1.0',
    'Content-type: text/html; charset=UTF-8',
    'From: Borrachorama <facturacion@borrachorama.com>',
    'Reply-To: facturacion@borrachorama.com',
    'X-Mailer: PHP/' . phpversion()
];

// Crear cuerpo HTML del email
$htmlBody = "
<!DOCTYPE html>
<html lang='es'>
<head>
    <meta charset='UTF-8'>
    <meta name='viewport' content='width=device-width, initial-scale=1.0'>
    <title>Factura Electrónica - Borrachorama</title>
    <style>
        body { font-family: Arial, sans-serif; background-color: #f5f5f5; margin: 0; padding: 20px; }
        .container { max-width: 600px; margin: 0 auto; background: white; padding: 30px; border-radius: 10px; }
        .header { text-align: center; border-bottom: 3px solid #ffb300; padding-bottom: 20px; }
        .logo { max-width: 200px; }
        .title { color: #d57c00; font-size: 24px; margin: 10px 0; }
        .content { margin: 20px 0; line-height: 1.6; }
        .button { display: inline-block; background: #4CAF50; color: white; padding: 12px 24px; text-decoration: none; border-radius: 5px; margin: 10px 0; }
        .footer { margin-top: 30px; padding-top: 20px; border-top: 1px solid #eee; font-size: 12px; color: #666; }
    </style>
</head>
<body>
    <div class='container'>
        <div class='header'>
            <img src='https://borrachorama.com/imagenes/Borrachoramalogo.png' alt='Borrachorama' class='logo'>
            <h1 class='title'>Factura Electrónica</h1>
            <p><strong>Folio:</strong> {$folio}</p>
        </div>

        <div class='content'>
            {$body}
        </div>

        <div style='text-align: center; margin: 30px 0;'>
            <a href='https://borrachorama.com/factura.html' class='button'>Ver Factura Completa</a>
        </div>

        <div class='footer'>
            <p><strong>Borrachorama, S.A. DE C.V.</strong></p>
            <p>Av. Cervecera No. 123, Col. Lúpulo, CDMX</p>
            <p>Tel: (55) 1234-5678 | Email: facturacion@borrachorama.com</p>
            <p>Este es un email automático, por favor no responder.</p>
        </div>
    </div>
</body>
</html>
";

// Intentar enviar el email
try {
    $success = mail($to, $subject, $htmlBody, implode("\r\n", $headers));

    if ($success) {
        echo json_encode([
            'success' => true,
            'message' => 'Factura enviada exitosamente por correo electrónico'
        ]);
    } else {
        http_response_code(500);
        echo json_encode([
            'success' => false,
            'message' => 'Error al enviar el email. Verifique la configuración del servidor.'
        ]);
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Error interno del servidor: ' . $e->getMessage()
    ]);
}
?>