<?php
// Archivo login.php

// Habilitar CORS (permitir solicitudes)
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Allow-Headers: Content-Type");
header("Access-Control-Allow-Credentials: true");
header("Content-Type: application/json");

// Importar la configuración de la base de datos desde database.php
require 'database.php';

// Resto del código de inicio de sesión
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Obtén los datos JSON de la solicitud POST
    $jsonData = file_get_contents("php://input");

    // Decodifica los datos JSON en un objeto o un arreglo asociativo
    $data = json_decode($jsonData, true);

    if ($data === null) {
        // Error al decodificar el JSON
        echo json_encode(["success" => false, "message" => "Error al decodificar el JSON"]);
    } else {
        // Verifica si los datos esperados están presentes en el arreglo
        if (isset($data['usernameOrEmail'], $data['password'])) {
            $usernameOrEmail = $data['usernameOrEmail'];
            $password = $data['password'];

            // Consulta los datos del usuario almacenados en la base de datos
            $sql = "SELECT id, nombre_completo, username, email, rol, contraseña FROM usuarios WHERE username = ? OR email = ?";
            try {
                $stmt = $pdo->prepare($sql);
                $stmt->execute([$usernameOrEmail, $usernameOrEmail]);
                $user = $stmt->fetch(PDO::FETCH_ASSOC);

                if ($user && password_verify($password, $user['contraseña'])) {
                    // Las credenciales son correctas, el usuario ha iniciado sesión con éxito

                    // Iniciar una sesión de PHP
                    session_start();

                    // Almacenar los datos del usuario en la sesión
                    $_SESSION['user'] = $user;

                    // Responder con un mensaje de inicio de sesión exitoso y los datos del usuario
                    echo json_encode(["success" => true, "message" => "Inicio de sesión exitoso", "user" => $user]);
                } else {
                    // Las credenciales son incorrectas, el inicio de sesión ha fallado
                    echo json_encode(["success" => false, "message" => "Credenciales incorrectas"]);
                }
            } catch (PDOException $e) {
                echo json_encode(["success" => false, "message" => "Error al consultar la base de datos: " . $e->getMessage()]);
            }
        } else {
            echo json_encode(["success" => false, "message" => "No se recibieron todos los datos del formulario"]);
        }
    }
} else {
    echo json_encode(["success" => false, "message" => "No se detectó una solicitud POST"]);
}
?>
