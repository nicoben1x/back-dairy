<?php
// Habilitar CORS (permitirr solicitudes
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST"); // Puedes ajustar los métodos HTTP permitidos según tu necesidad
header("Access-Control-Allow-Headers: Content-Type");
header("Access-Control-Allow-Credentials: true"); // Si deseas permitir el envío de cookies
header("Content-Type: application/json"); // Establece el tipo de contenido de la respuesta

// Resto del código PHP aquí
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

require 'vendor/autoload.php'; // Asegúrate de que la ruta sea la correcta para incluir PHPMailer

// Importar la configuración de la base de datos desde database.php
require 'database.php';

// Resto del código de registro
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Obtén los datos JSON de la solicitud POST
    $jsonData = file_get_contents("php://input");

    // Decodifica los datos JSON en un objeto o un arreglo asociativoo
    $data = json_decode($jsonData, true); // El segundo parámetro true convierte en arreglo asociativo

    if ($data === null) {
        // Error al decodificar el JSON
        echo json_encode(["message" => "Error al decodificar el JSON"]);
    } else {
        // Verifica si los datos esperados están presentes en el arregloo
        if (
            isset($data['nombre_completo'], $data['username'], $data['email'], $data['nuevaContraseña'], $data['repetirContraseña'])
        ) {
            $nombre_completo = $data['nombre_completo'];
            $username = $data['username'];
            $email = $data['email'];
            $nuevaContraseñaPrevia = $data['nuevaContraseña'];
            $nuevaContraseña = password_hash($data['nuevaContraseña'], PASSWORD_DEFAULT); // Cifra la contraseña
            $repetirContraseña = $data['repetirContraseña'];

            // Verifica si las contraseñas coinciden
            if ($nuevaContraseñaPrevia !== $repetirContraseña) {
                echo json_encode(["message" => "Las contraseñas no coinciden"]);
                echo $nombre_completo;
                echo $username;
                echo $email;
                echo $nuevaContraseña;
                echo $repetirContraseña;
            } else {
                // Verifica si el nombre de usuario o el correo electrónico ya están registrados
                $sql_check = "SELECT COUNT(*) as count FROM usuarios WHERE username = ? OR email = ?";
                try {
                    $stmt_check = $pdo->prepare($sql_check);
                    $stmt_check->execute([$username, $email]);
                    $result = $stmt_check->fetch(PDO::FETCH_ASSOC);
                    if ($result['count'] > 0) {
                        echo json_encode(["message" => "El nombre de usuario o el correo electrónico ya están en uso"]);
                    } else {
                        // Inserta los datos en la base de datoss
                        $rol = "Cliente"; // Valor predeterminado para el rol

                        $sql_insert = "INSERT INTO usuarios (nombre_completo, username, email, contraseña, rol)
                               VALUES (?, ?, ?, ?, ?)";
                        try {
                            $stmt_insert = $pdo->prepare($sql_insert);
                            $stmt_insert->execute([$nombre_completo, $username, $email, $nuevaContraseña, $rol]);
                            echo json_encode(["message" => "Registro exitoso"]);

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

                $mail->Subject = 'Registro de Usuario';
                $body = 'Se ha registrado un nuevo usuario.';

               
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



                        } catch (PDOException $e) {
                            echo json_encode(["message" => "Error al registrar: " . $e->getMessage()]);
                        }
                    }
                } catch (PDOException $e) {
                    echo json_encode(["message" => "Error al verificar la existencia de usuario o correo: " . $e->getMessage()]);
                }
            }
        } else {
            echo json_encode(["message" => "No se recibieron todos los datos del formulario"]);
        }
    }
} else {
    echo json_encode(["message" => "No se detectó una solicitud POST"]);
}

?>