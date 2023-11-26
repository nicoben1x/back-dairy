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
        $fecha = $data['fecha'];

        // Obtén el valor de "usuariostock" del JSON
        $usuariostock = $data['usuariostock'];



        // Conexión a la base de datos (utilizando la configuración de database.php)
        $conn = $pdo;

        if ($conn) {
            // Procesar los datos de quimicoStock
            if (isset($data['quimicoStock']) && is_array($data['quimicoStock'])) {
                // Actualizar la columna "fecha" en el primer elemento de quimicoStock
                if (!empty($fecha)) {
                    $primerElemento = reset($data['quimicoStock']); // Obtiene el primer elemento
                    $id = $primerElemento['id'];

                    // Actualiza la columna "fecha" en la base de datos
                    $sql = "UPDATE quimicoStock SET fecha = ? WHERE id = ?";
                    $stmt = $conn->prepare($sql);
                    $stmt->execute([$fecha, $id]);
                }

                // Continúa procesando los demás elementos de quimicoStock...
                foreach ($data['quimicoStock'] as $quimico) {
                    $id = $quimico['id'];

                    // Verifica si el registro ya existe en la base de datos
                    $sql = "SELECT id FROM quimicoStock WHERE id = ?";

                    // Prepara y ejecuta la consulta
                    $stmt = $conn->prepare($sql);
                    $stmt->execute([$id]);

                    if ($stmt->fetchColumn() > 0) {
                        // El registro ya existe, por lo que realizamos una actualización en lugar de una inserción
                        $sql = "UPDATE quimicoStock SET 
                            PRODUCTO = ?,
                            PROVEEDOR = ?,
                            cantidads = ?,
                            presentacions = ?,
                            cantidada = ?,
                            presentaciona = ?,
                            cantidadp = ?,
                            presentacionp = ?,
                            total = ?,
                            Ubicación = ?,
                            FORMATO = ?
                            WHERE id = ?";

                        // Luego, ejecuta la consulta con los valores correspondientes
                        $stmt = $conn->prepare($sql);
                        $stmt->execute([
                            $quimico['PRODUCTO'],
                            $quimico['PROVEEDOR'],
                            $quimico['cantidads'],
                            $quimico['presentacions'],
                            $quimico['cantidada'],
                            $quimico['presentaciona'],
                            $quimico['cantidadp'],
                            $quimico['presentacionp'],
                            $quimico['total'],
                            $quimico['Ubicación'],
                            $quimico['FORMATO'],
                            $id
                        ]);
                    } else {
                        // El registro no existe, realiza una inserción
                        $sql = "INSERT INTO quimicoStock (id, PRODUCTO, PROVEEDOR, cantidads, presentacions, cantidada, presentaciona, cantidadp, presentacionp, total, Ubicación, FORMATO, fecha) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
                        
                        // Luego, ejecuta la consulta con los valores correspondientes
                        $stmt = $conn->prepare($sql);
                        $stmt->execute([
                            $id,
                            $quimico['PRODUCTO'],
                            $quimico['PROVEEDOR'],
                            $quimico['cantidads'],
                            $quimico['presentacions'],
                            $quimico['cantidada'],
                            $quimico['presentaciona'],
                            $quimico['cantidadp'],
                            $quimico['presentacionp'],
                            $quimico['total'],
                            $quimico['Ubicación'],
                            $quimico['FORMATO'],
                            $quimico['fecha']
                        ]);
                    }
                }
            }

            // Procesar los datos de bidonesStock
            if (isset($data['bidonesStock']) && is_array($data['bidonesStock'])) {
                foreach ($data['bidonesStock'] as $bidon) {
                    $id = $bidon['id'];

                    // Verifica si el registro ya existe en la base de datos
                    $sql = "SELECT id FROM bidonesStock WHERE id = ?";

                    // Prepara y ejecuta la consulta
                    $stmt = $conn->prepare($sql);
                    $stmt->execute([$id]);

                    if ($stmt->fetchColumn() > 0) {
                        // El registro ya existe, por lo que realizamos una actualización en lugar de una inserción
                        $sql = "UPDATE bidonesStock SET 
                            producto = ?,
                            cantidads = ?,
                            presentacions = ?,
                            cantidada = ?,
                            presentaciona = ?,
                            cantidadp = ?,
                            presentacionp = ?,
                            total = ?,
                            ubicacion = ?
                            WHERE id = ?";

                        // Luego, ejecuta la consulta con los valores correspondientes
                        $stmt = $conn->prepare($sql);
                        $stmt->execute([
                            $bidon['producto'],
                            $bidon['cantidads'],
                            $bidon['presentacions'],
                            $bidon['cantidada'],
                            $bidon['presentaciona'],
                            $bidon['cantidadp'],
                            $bidon['presentacionp'],
                            $bidon['total'],
                            $bidon['ubicacion'],
                            $id
                        ]);
                    } else {
                        // El registro no existe, realiza una inserción
                        $sql = "INSERT INTO bidonesStock (id, producto, cantidads, presentacions, cantidada, presentaciona, cantidadp, presentacionp, total, ubicacion) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

                        // Luego, ejecuta la consulta con los valores correspondientes
                        $stmt = $conn->prepare($sql);
                        $stmt->execute([
                            $id,
                            $bidon['producto'],
                            $bidon['cantidads'],
                            $bidon['presentacions'],
                            $bidon['cantidada'],
                            $bidon['presentaciona'],
                            $bidon['cantidadp'],
                            $bidon['presentacionp'],
                            $bidon['total'],
                            $bidon['ubicacion']
                        ]);
                    }
                }
            }

            // Si la operación fue exitosa, envía una respuesta de éxito
            $response = array('success' => true, 'message' => 'Cambios guardados exitosamente');
            echo json_encode($response);
            




                        // ... Tu código para actualizar la base de datos ...

// Envía un correo electrónico después de actualizar la base de 




    $mail = new PHPMailer(true);
    try {
        // Configurar la conexión SMTP
        $mail->isSMTP();
        $mail->Host = 'mail.dairy.com.ar'; // Cambia esto a tu servidor SMTP
        $mail->SMTPAuth = true;
        $mail->Username = 'nico@dairy.com.ar'; // Cambia esto a tu dirección de correo
        $mail->Password = 'tomatE77!'; // Cambia esto a tu contraseña de correo
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = 587; // Cambia esto al puerto SMTP adecuado

        // Configurar la codificación de caracteres a UTF-8
        $mail->CharSet = 'UTF-8';

        // Detalles del correo electrónico
        $mail->setFrom('nico@dairy.com.ar', 'Nico Dairy');
        $mail->addAddress('nicoben1x@gmail.com', 'Nicoben');
        $mail->Subject = 'Actualización de Stock';

       

        $mail->Body = 'Se ha actualizado el stock exitosamente. Actualizado por ' . $data['usuariomod2'] . ".\n";

        // Agregar la información de los pedidos de productos químicos
        if (isset($data['productosQuimicos']) && is_array($data['productosQuimicos'])) {
            $mail->Body .= "\n\nProductos a Comprar:\n\n";
            foreach ($data['productosQuimicos'] as $productoQuimico) {
                $mail->Body .= "Producto: " . $productoQuimico['nombre'] . "\n"; // 'nombre' en lugar de 'PRODUCTO'
                $mail->Body .= "Proveedor: " . $productoQuimico['proveedor'] . "\n"; // 'proveedor' en lugar de 'PROVEEDOR'
                $mail->Body .= "Cantidad: " . $productoQuimico['cantidadPedido'] . "\n";
                $mail->Body .= "Presentación Kg : " . $productoQuimico['presentacionKgPedido'] . "\n";
                $mail->Body .= "Total: " . $productoQuimico['total'] . " Kg\n";
                $mail->Body .= "Ubicación: " . $productoQuimico['ubicacion'] . "\n"; // 'ubicacion' en lugar de 'Ubicación'
                $mail->Body .= "Formato: " . $productoQuimico['formato'] . "\n"; // 'formato' en lugar de 'FORMATO'
                // Si la fecha está disponible en los datos, puedes agregarla también
                // $mail->Body .= "Fecha: " . $productoQuimico['fecha'] . "\n";
                $mail->Body .= "\n";
            }
        }
        
        // Agregar la información de los pedidos de bidones
        if (isset($data['productosBidones']) && is_array($data['productosBidones'])) {
           
            foreach ($data['productosBidones'] as $productoBidon) {
                $mail->Body .= "Producto: " . $productoBidon['nombre'] . "\n"; // 'nombre' en lugar de 'producto'
                $mail->Body .= "Cantidad: " . $productoBidon['cantidadPedido'] . "\n";
                $mail->Body .= "Presentación Kg: " . $productoBidon['presentacionKgPedido'] . "\n";
                $mail->Body .= "Total: " . $productoBidon['total'] . "\n";
       
                $mail->Body .= "\n";
            }
        }

        // Configuración adicional para el envío del correo
        $mail->SMTPOptions = [
            'ssl' => [
                'verify_peer' => false,
                'verify_peer_name' => false,
                'allow_self_signed' => true,
            ],
        ];

        // Envío del correo electrónico
        $mail->send();

        // Respuesta de éxito
        // Aquí puedes retornar algo o ejecutar acciones adicionales si el envío es exitoso

    } catch (Exception $e) {
        // Manejo de errores
        // Aquí puedes manejar los errores generados durante el envío del correo electrónico
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











