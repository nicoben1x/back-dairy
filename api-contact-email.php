<?php
// Habilitar CORS (permitir solicitudes desde cualquier origen)
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST"); // Puedes ajustar los métodos HTTP permitidos según tu necesidad
header("Access-Control-Allow-Headers: Content-Type");
header("Access-Control-Allow-Credentials: true"); // Si deseas permitir el envío de cookies
header("Content-Type: application/json"); // Establece el tipo de contenido de la respuesta



use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

require 'vendor/autoload.php'; // Asegúrate de que la ruta sea la correcta para incluir PHPMailer







// Importar la configuración de la base de datos desde database.php
require 'database.php';

// Verificar si se ha recibido una solicitud POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Obtén los datos JSON de la solicitud POST
    $jsonData = file_get_contents("php://input");

    // Decodifica los datos JSON en un objeto o un arreglo asociativo
    $data = json_decode($jsonData, true);

    if ($data === null) {
        // Error al decodificar el JSON
        echo json_encode(["message" => "Error al decodificar el JSON"]);
    } else {



        $mail = new PHPMailer(true);
try {
    
    
    $mail->isSMTP();
    $mail->Host = 'mail.dairy.com.ar'; // Cambia esto a tu servidor SMTP
    $mail->SMTPAuth = true;
    $mail->Username = 'nico@dairy.com.ar'; // Cambia esto a tu dirección de correo
    $mail->Password = 'tomatE77!'; // Cambia esto a tu contraseña de correo
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
    $mail->Port = 587; // Cambia esto al puerto SMTP adecuado

       // Configurar la codificación de caracteres a UTF-8
       $mail->CharSet = 'UTF-8';

    $mail->setFrom('nico@dairy.com.ar', 'Nico Dairy');
    $mail->addAddress('nuevonnncuenta@gmail.com', 'Nicoben');

    $nombre = $data['nombre'];
    $telefono = $data['telefono'];
    $email = $data['email'];
    $consulta = $data['consulta'];

   
    
    $mail->Subject = 'Consulta Web Dairy';
    $mail->Body = "Nombre: $nombre. \nTeléfono: $telefono.\nEmail: $email.\nConsulta: $consulta.";

    $mail->SMTPOptions = [
        'ssl' => [
            'verify_peer' => false,
            'verify_peer_name' => false,
            'allow_self_signed' => true,
        ],
    ];
    
    $mail->send();


    // Envío exitoso
    $response = array('success' => true, 'message' => 'Correo enviado con éxito');
    echo json_encode($response);



        // Respuesta de éxito
       
    } catch (Exception $e) {
        // Manejo de erroress
        $response = array('success' => false, 'message' => 'Error al enviar el correo: ' . $mail->ErrorInfo);
    echo json_encode($response);
       
    }



    }
} else {
    echo json_encode(["message" => "No se detectó una solicitud POST"]);
}




?>