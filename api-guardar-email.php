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
        
         // Obtén el valor de "nombre" y "email" del JSON
         $nombre = $data['nombre'];
         $email = $data['email'];

         // Convierte el nombre a mayúsculas
         $nombre = strtoupper($nombre);
 

    

        // Conexión a la base de datos (utilizando la configuración de database.php)
        $conn = $pdo;

        if ($conn) {
            // Procesar los datos de quimicoStock
            // Insertar los datos en la base de datos
            $sql = "INSERT INTO ClientesEmail (nombre, email) VALUES (?, ?)";
            $stmt = $conn->prepare($sql);
            if ($stmt->execute([$nombre, $email])) {
                // Si la operación fue exitosa, envía una respuesta de éxito
                $response = array('success' => true, 'message' => 'Datos guardados exitosamente');
                echo json_encode($response);
            } else {
                // Error al insertar en la base de datos
                $response = array('success' => false, 'message' => 'Error al guardar los datos en la base de datos');
                echo json_encode($response);
            }
        } else {
            // Error de conexión a la base de datos
            $response = array('success' => false, 'message' => 'Error de conexión a la base de datos');
            echo json_encode($response);
        }
    }
} else {
    // Si la solicitud no es una solicitud POST, enviar una respuesta de error
    $response = array('success' => false, 'message' => 'Método no permitido');
    echo json_encode($response);
}
?>