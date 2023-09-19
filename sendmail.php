<?php

// Habilitar CORS (permitir solicitudes)
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Allow-Headers: Content-Type");
header("Access-Control-Allow-Credentials: true");
header("Content-Type: application/json");

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

require 'vendor/autoload.php'; // Asegúrate de incluir la biblioteca PHPMailer

// Leer los datos del formulario desde la solicitud POST
$inputData = file_get_contents("php://input");
$requestData = json_decode($inputData, true);

// Verificar si los datos se han recibido correctamente
if (empty($requestData)) {
    echo json_encode(["success" => false, "message" => "Datos del formulario incompletos"]);
    exit;
}

$cliente = $requestData['cliente'];
$pagos = $requestData['pagos'];

// Destinatario fijo (administración)
$destinatarioAdmin = 'testprogramacion2023@outlook.com';

// Destinatario variable (cliente)
$destinatarioCliente = $cliente; // Supongamos que el valor del campo cliente contiene la dirección de correo

// Crear instancia de PHPMailer
$mail = new PHPMailer(true);

try {
    // Configurar el servidor SMTP
    $mail->isSMTP();
    $mail->Host = 'mail.dairy.com.ar'; // Cambia esto al servidor SMTP adecuado
    $mail->SMTPAuth = true;
    $mail->Username = 'nico@dairy.com.ar';
    $mail->Password = 'tomatE77!';
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS; // Puedes cambiar esto según tus necesidades
    $mail->Port = 587; // Puerto SMTP adecuado

    // Configurar la codificación de caracteres a UTF-8
    $mail->CharSet = 'UTF-8';

    // Configurar los remitentes y destinatarios
    $mail->setFrom('nico@dairy.com.ar', 'Nico Dairy');
    $mail->addAddress($destinatarioAdmin);
    $mail->addAddress($destinatarioCliente);

    // Asunto y cuerpo del correo
    $mail->Subject = 'Información de Pagos para ' . $cliente;
    $mail->Body = 'Datos del formulario: ' . json_encode($pagos);


    $mail->SMTPOptions = [
        'ssl' => [
            'verify_peer' => false,
            'verify_peer_name' => false,
            'allow_self_signed' => true,
        ],
    ];

    // Crear una tabla HTML para mostrar los datos del formulario
$tableHtml = '<table>';
foreach ($pagos as $pago) {
    $tableHtml .= '<tr>';
    $tableHtml .= '<td>Precio: ' . $pago['precio'] . '</td>';
    $tableHtml .= '<td>Tipo de Pago: ' . $pago['tipoPago'] . '</td>';
    // Puedes continuar agregando más datos aquí
    $tableHtml .= '</tr>';
}
$tableHtml .= '</table>';

// Configurar el cuerpo del correo
$mail->Body = 'Datos del formulario:<br>' . $tableHtml;
$mail->IsHTML(true); // Indicar que el correo contiene HTML

// Resto de tu código...

    // Envía el correo
    $mail->send();

    // Respuesta de éxito
    echo json_encode(["success" => true, "message" => "Correo enviado con éxito backend"]);
} catch (Exception $e) {
    // Manejo de errores
    echo json_encode(["success" => false, "message" => "Error al enviar el correo: " . $mail->ErrorInfo]);
}
?>
