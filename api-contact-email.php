<?php

// Habilitar CORS (permitirr solicitudes
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST"); // Puedes ajustar los métodos HTTP permitidos según tu necesidad
header("Access-Control-Allow-Headers: Content-Type");
header("Access-Control-Allow-Credentials: true"); // Si deseas permitir el envío de cookies
header("Content-Type: application/json"); // Establece el tipo de contenido de la respuesta

// Resto del código PHP aquí


if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Obtén los datos JSON de la solicitud POST
    $jsonData = file_get_contents("php://input");

    // Decodifica los datos JSON en un objeto o un arreglo asociativo
    $data = json_decode($jsonData, true);

    if ($data === null) {
        echo json_encode(["message" => "Error al decodificar el JSON"]);
    } else {
        if (isset($data['nombre'], $data['telefono'], $data['email'], $data['consulta'])) {
            $nombre = $data['nombre'];
            $telefono = $data['telefono'];
            $email = $data['email'];
            $consulta = $data['consulta'];

            $destinatario = 'nicoben1x@gmail.com';
            $asunto = 'Nueva consulta de contacto';
            $mensaje = "Nombre: $nombre\nTeléfono: $telefono\nEmail: $email\nConsulta: $consulta";



            


            // Configura la información del servidor SMTP
    ini_set('SMTP', 'mail.dairy.com.ar'); // Reemplaza 'tu_servidor_SMTP' por el servidor SMTP que debes usar
    ini_set('smtp_port', '587'); // Reemplaza 'tu_puerto_SMTP' por el puerto SMTP correspondiente

    // Envia el correo electrónico

            // Configura la dirección de retorno (Return-Path)
            $headers = 'From: ' . $email . "\r\n" .
                'Reply-To: ' . $email . "\r\n" .
                'Return-Path: ' . $email . "\r\n" .
                'X-Mailer: PHP/' . phpversion();

            // Envia el correo electrónico con los encabezados
            $enviado = mail($destinatario, $asunto, $mensaje, $headers);

            if ($enviado) {
                echo json_encode(['message' => 'Correo enviado con éxito']);
            } else {
                echo json_encode(['error' => 'Error al enviar el correo']);
            }
        } else {
            echo json_encode(["message" => "No se recibieron todos los datos del formulario"]);
        }
    }
} else {
    echo json_encode(["message" => "No se detectó una solicitud POST"]);
}
?>