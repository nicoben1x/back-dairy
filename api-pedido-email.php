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
        // Obtén el valor de "selectedItems" y "usuariopedido" del JSON
        $selectedItems = $data['selectedItems'];
        $usuariopedido = $data['usuariopedido'];
         // Obtén la dirección de correo del cliente
        $clienteEmail = $data['clienteEmail'];

        // Conexión a la base de datos (utilizando la configuración de database.php)
        $conn = $pdo;

        if ($conn) {
            // Si la operación fue exitosa, envía una respuesta de éxito
            $response = array('success' => true, 'message' => 'Cambios guardados exitosamente');
            echo json_encode($response);

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

                // Configurar la codificación de caracteres a UTF-8
                $mail->CharSet = 'UTF-8';

                $mail->setFrom('nico@dairy.com.ar', 'Nico Dairy');
                $mail->addAddress('nicoben1x@gmail.com', 'Nicoben');
                $mail->addAddress($clienteEmail, 'Cliente');

                $mail->Subject = 'Pedido Dairy Web';
                $body = 'Se ha realizado el siguiente pedido por ' . $usuariopedido . ".\n\n";

                foreach ($selectedItems as $item) {
                    $body .= "Código: " . $item['codigo'] . ".\n";
                    $body .= "Producto: " . $item['producto'] . ".\n";
                    $body .= "Precio Unitario: $" . $item['precio'] . ".\n";
                    $body .= "Cantidad: " . $item['cantidad'] . ".\n";
                    $body .= "Precio Total: $" . $item['precioTotalProducto'] . ".\n\n";
                }

                // Agregar el precio total final al correo
                $body .= "Precio Total Final Estimado: $" . $data['precioTotalFinal'] . ".\n";

                $mail->Body = $body;

                $mail->SMTPOptions = [
                    'ssl' => [
                        'verify_peer' => false,
                        'verify_peer_name' => false,
                        'allow_self_signed' => true,
                    ],
                ];

                $mail->send();
            } catch (Exception $e) {
                // Manejo de errores en el envío del correo
                echo json_encode(["success" => false, "message" => "Error al enviar el correo electrónico: " . $mail->ErrorInfo]);
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
