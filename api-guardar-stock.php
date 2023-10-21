<?php
// Habilitar CORS (permitir solicitudes desde cualquier origen)
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST"); // Puedes ajustar los métodos HTTP permitidos según tu necesidad
header("Access-Control-Allow-Headers: Content-Type");
header("Access-Control-Allow-Credentials: true"); // Si deseas permitir el envío de cookies
header("Content-Type: application/json"); // Establece el tipo de contenido de la respuesta

// Importar la configuración de la base de datos desde database.php
require 'database.php';

// Verificar si se ha recibido una solicitud POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Obtén los datos JSON de la solicitud POST
    $jsonData = file_get_contents("php://input");

    // Decodifica los datos JSON en un objeto o un arreglo asociativo
    $data = json_decode($jsonData, true); // El segundo parámetro true convierte en arreglo asociativo

    if ($data === null) {
        // Error al decodificar el JSON
        echo json_encode(["message" => "Error al decodificar el JSON"]);
    } else {
        // Conexión a la base de datos (utilizando la configuración de database.php)
        $conn = $pdo;

        if ($conn) {
            // Procesar los datos de quimicoStock
            if (isset($data['quimicoStock']) && is_array($data['quimicoStock'])) {
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
                        $sql = "INSERT INTO quimicoStock (id, PRODUCTO, PROVEEDOR, cantidads, presentacions, cantidada, presentaciona, cantidadp, presentacionp, total, Ubicación, FORMATO) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
                        
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
                            $quimico['FORMATO']
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
