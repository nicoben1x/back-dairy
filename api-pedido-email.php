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
        // Obtén el valor de "fecha" del JSON
        $selectedItems = $data['selectedItems'];

        // Obtén el valor de "usuariostock" del JSON
        $usuariopedido = $data['usuariopedido'];

        // Conexión a la base de datos (utilizando la configuración de database.php)
        $conn = $pdo;

        if ($conn) {
            

            // Si la operación fue exitosa, envía una respuesta de éxito
            $response = array('success' => true, 'message' => 'Cambios guardados exitosamente');
            echo json_encode($response);
            




                        // ... Tu código para actualizar la base de datos ...

// Envía un correo electrónico después de actualizar la base de datos

$mail = new PHPMailer(true);
try {
    
    
    $mail->isSMTP();
    $mail->Host = 'mail.dairy.com.ar'; // Cambia esto a tu servidor SMTP
    $mail->SMTPAuth = true;
    $mail->Username = 'nico@dairy.com.ar'; // Cambia esto a tu dirección de correo
    $mail->Password = 'tomatE77!'; // Cambia esto a tu contraseña de correo
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
    $mail->Port = 587; // Cambia esto al puerto SMTP adecuado

       // Configurar laa codificación de caracteres a UTF-8
       $mail->CharSet = 'UTF-8';

    $mail->setFrom('nico@dairy.com.ar', 'Nico Dairy');
    $mail->addAddress('nuevonnncuenta@gmail.com', 'Nicoben');

    
    $mail->Subject = 'Pedido Dairy Web';
$body = 'Se ha sido realizado el siguiente pedido. Realizado por ' . json_encode($usuariopedido) . ".\n\n";

foreach ($selectedItems as $item) {
    $product = $item['item'];
    $quantity = $item['quantity'];
    $body .= "Producto: " . $product['description'] . ".\n";
    $body .= "Código: " . $product['code'] . ".\n";
    $body .= "Presentación: " . $product['presentation'] . ".\n";
    $body .= "Cantidad: " . $quantity . ".\n\n";
}

$mail->Body = $body;

    $mail->SMTPOptions = [
        'ssl' => [
            'verify_peer' => false,
            'verify_peer_name' => false,
            'allow_self_signed' => true,
        ],
    ];

    $mail->send();



        // Respuesta de éxito
       
    } catch (Exception $e) {
        // Manejo de erroress
       
    }












        } else {
            // Error de conexión a la base de datos
            $response = array('success' => false, 'message' => 'Error de conexión a la base de datos pedidos.');
            echo json_encode($response);
        }
    }
} else {
    // Si la solicitud no es una solicitud POST, enviar una respuesta de error
    $response = array('success' => false, 'message' => 'Método no permitido');
    echo json_encode($response);
}
?>
