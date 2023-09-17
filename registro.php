<?php

// Habilitar CORS (permitir solicitudes
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST"); // Puedes ajustar los métodos HTTP permitidos según tu necesidad
header("Access-Control-Allow-Headers: *");
header("Access-Control-Allow-Credentials: true"); // Si deseas permitir el envío de cookies
header("Content-Type: application/json"); // Establece el tipo de contenido de la respuesta


// Resto del código PHP aquí

// Importar la configuración de la base de datos desde database.php
require 'database.php';



// Resto del código de registro// Resto del código de registro
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    global $_POST;

    
    // Verifica si las variables POST están definidas
    if (
        isset($_POST['nombreCompleto'], $_POST['nuevoUsername'], $_POST['email'], $_POST['nuevaContraseña'], $_POST['repetirContraseña'])
    ) {
        $nombreCompleto = $_POST['nombreCompleto'];
        $nuevoUsername = $_POST['nuevoUsername'];
        $email = $_POST['email'];
        $nuevaContraseña = password_hash($_POST['nuevaContraseña'], PASSWORD_DEFAULT); // Cifra la contraseña
        $repetirContraseña = $_POST['repetirContraseña'];
    
        echo "Nombre Completo: " . $nombreCompleto . "<br>";
        echo "Nuevo Username: " . $nuevoUsername . "<br>";
        echo "Email: " . $email . "<br>";
        echo "Nueva Contraseña: " . $nuevaContraseña . "<br>";
        echo "Repetir Contraseña: " . $repetirContraseña . "<br>";
    

        // Verifica si las contraseñas coinciden
        if ($nuevaContraseña !== password_hash($repetirContraseña, PASSWORD_DEFAULT)) {
            echo "Las contraseñas no coinciden";
            exit;
        }

        // Verifica si el nombre de usuario o el correo electrónico ya están registrados
        $sql_check = "SELECT COUNT(*) as count FROM usuarios WHERE nuevoUsername = ? OR email = ?";
        try {
            $stmt_check = $pdo->prepare($sql_check);
            $stmt_check->execute([$nuevoUsername, $email]);
            $result = $stmt_check->fetch(PDO::FETCH_ASSOC);
            if ($result['count'] > 0) {
                echo "El nombre de usuario o el correo electrónico ya están en uso";
                exit;
            }
        } catch (PDOException $e) {
            echo "Error al verificar la existencia de usuario o correo: " . $e->getMessage();
            exit;
        }

        // Inserta los datos en la base de datos
        $rol = "Normal"; // Valor predeterminado para el rol

        $sql_insert = "INSERT INTO usuarios (nombreCompleto, nuevoUsername, email, contraseña, rol)
               VALUES (?, ?, ?, ?, ?)";
        try {
            $stmt_insert = $pdo->prepare($sql_insert);
            $stmt_insert->execute([$nombreCompleto, $nuevoUsername, $email, $nuevaContraseña, $rol]);
            echo "Registro exitoso";
        } catch (PDOException $e) {
            echo "Error al registrar: " . $e->getMessage();
        }
    } else {
        
        echo "No se recibieron todos los datos del formulario";
        echo $nombreCompleto;
        echo $nuevoUsername;
        echo $email;
        echo $nuevaContraseña; 
        echo $repetirContraseña;
    }
}else{
    echo "No se detecto un request POST";
}
?>