<?php

// Habilita CORS para permitirr solicitudes desde https://new.dairy.com.ar
header("Access-Control-Allow-Origin: https://new.dairy.com.ar");


require 'database.php'; // Reemplaza 'database.php' con el nombre de tu archivo de configuración de base de datos

function getProducts() {
    global $pdo; // $pdo debe ser la instancia de tu conexión PDO

    try {
        $query = "SELECT * FROM productNormal";
        $statement = $pdo->prepare($query);
        $statement->execute();
        $result = $statement->fetchAll(PDO::FETCH_ASSOC);

        // Convierte el resultado en formato JSON y lo envía como respuesta
        header('Content-Type: application/json');
        echo json_encode($result);
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode(['message' => 'Something goes wrong (ProductNormal)']);
    }
}

// Llama a la función para obtener los productos
getProducts();
?>
