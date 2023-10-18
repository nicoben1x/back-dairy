<?php

// Habilitar CORS (permitir solicitudes)


header('Access-Control-Allow-Origin: *');
header("Access-Control-Allow-Methods: HEAD, GET, POST, PUT, PATCH, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: X-API-KEY, Origin, X-Requested-With, Content-Type, Accept, Access-Control-Request-Method,Access-Control-Request-Headers, Authorization");
header('Content-Type: application/json');
$method = $_SERVER['REQUEST_METHOD'];
if ($method == "OPTIONS") {
header('Access-Control-Allow-Origin: *');
header("Access-Control-Allow-Headers: X-API-KEY, Origin, X-Requested-With, Content-Type, Accept, Access-Control-Request-Method,Access-Control-Request-Headers, Authorization");
header("HTTP/1.1 200 OK");
die();
}

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
$nombreCliente = $requestData['nombreCliente'];
$pagos = $requestData['pagos'];

// Calcular el total de los pagos
$totalPagos = 0;
foreach ($pagos as $pago) {
    $totalPagos += floatval($pago['precio']);
}

// Destinatario fijo (administración)
$destinatarioAdmin = 'testprogramacion2023@outlook.com';
$destinatarioAdmin2 = 'nuevonnncuenta@gmail.com';

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
    $mail->addAddress($destinatarioAdmin2);
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
    $tableHtml .= '<tr>';
    $tableHtml .= '<td>Estimado '  . $nombreCliente . '. Le informamos que hemos recibido el pago correspondiente para acreditar a su cuenta corriente. Agradecemos su dedicación y profesionalismo en nuestra relación comercial. Este es un mensaje automático.<br><br></td>';
    $tableHtml .= '</tr>';
    $tableHtml .= '<tr>';
    $tableHtml .= '<td>Datos de pago:</td>';
    $tableHtml .= '</tr>';

foreach ($pagos as $pago) {
    $tableHtml .= '<tr>';
    $tableHtml .= '<td>Tipo de Pago: ' . $pago['tipoPago'] . '.</td>';
    $tableHtml .= '<td>Valor: $' . $pago['precio'] . '</td>';
    
    // Puedes continuar agregando más datos aquí
    $tableHtml .= '</tr>';
}

// Agregar una fila vacía como salto de línea antes del total de los pagos
$tableHtml .= '<tr><td></td></tr>';

// Agregar el total de los pagos al correo
$tableHtml .= '<tr>';
$tableHtml .= '<td>Total: $' . $totalPagos . '</td>';
$tableHtml .= '</tr>';





foreach ($pagos as $index => $pago) {
    if (!empty($pago['imagen'])) {
        // Eliminar la parte inicial "data:image/jpeg;base64,"
        $imagenData = substr($pago['imagen'], strpos($pago['imagen'], ',') + 1);
        
        // Decodificar la imagen base64
        $imagenData = base64_decode($imagenData);
        
        $imagenPath = './imagenesmail/' . $index . '.jpg'; // Cambia la extensión según el tipo de imagen
        file_put_contents($imagenPath, $imagenData); // Guardar la imagen en el servidor
        $mail->addAttachment($imagenPath); // Adjuntar la imagen al correo
     
    }
}



$tableHtml .= '</table>';

// Configurarr el cuerpo del correo
$mail->Body = $tableHtml;
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
