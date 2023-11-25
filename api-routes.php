<?php

// Habilita CORS para permitir solicitudes desde https://new.dairy.com.ar y otras
// La IP es de computadora hogar.
$allowedOrigins = [
    "https://dairy.com.ar",
    "http://192.168.100.40:3000",
    "http://localhost:3000"
];

$origin = $_SERVER['HTTP_ORIGIN'];

if (in_array($origin, $allowedOrigins)) {
    header("Access-Control-Allow-Origin: $origin");
}


// Resto de tu código aquí....

require 'database.php'; // Reemplaza 'database.php' con el nombre de tu archivo de configuración de base de datos

function getProducts() {
    global $pdo; // $pdo debe ser la instancia de tu conexión PDO

    try {
        $query = "SELECT * FROM productNormal";
        $statement = $pdo->prepare($query);
        $statement->execute();
        $products = $statement->fetchAll(PDO::FETCH_ASSOC);

        return $products;
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode(['message' => 'Something goes wrong (ProductNormal)']);
        exit; // Termina la ejecución del script si hay un error
    }
}

function getQuimicos() {
    global $pdo; // $pdo debe ser la instancia de tu conexión PDO

    try {
        $query = "SELECT * FROM quimicoNormal";
        $statement = $pdo->prepare($query);
        $statement->execute();
        $quimicos = $statement->fetchAll(PDO::FETCH_ASSOC);

        return $quimicos;
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode(['message' => 'Something goes wrong (QuimicoNormal)']);
        exit; // Termina la ejecución del script si hay un error
    }
}

// Obtén los datos de productos y químicos por separado
$productsData = getProducts();
$quimicosData = getQuimicos();

// Convierte los datos en formato JSON y los envía como respuesta
$response = [
    'products' => $productsData,
    'quimicos' => $quimicosData
];

header('Content-Type: application/json');
echo json_encode($response);


?>
