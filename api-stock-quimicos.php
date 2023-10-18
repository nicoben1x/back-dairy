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

function getquimicoStock() {
    global $pdo; // $pdo debe ser la instancia de tu conexión PDO

    try {
        $query = "SELECT * FROM quimicoStock";
        $statement = $pdo->prepare($query);
        $statement->execute();
        $quimicoStock = $statement->fetchAll(PDO::FETCH_ASSOC);

        return $quimicoStock;
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode(['message' => 'Something goes wrong (quimicoStock)']);
        exit; // Termina la ejecución del script si hay un error
    }
}


function getbidonesStock() {
    global $pdo; // $pdo debe ser la instancia de tu conexión PDO

    try {
        $query = "SELECT * FROM bidonesStock";
        $statement = $pdo->prepare($query);
        $statement->execute();
        $bidonesStock = $statement->fetchAll(PDO::FETCH_ASSOC);

        return $bidonesStock;
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode(['message' => 'Something goes wrong (bidonesStock)']);
        exit; // Termina la ejecución del script si hay un error
    }
}



// Obtén los datos de quimicoStock
$quimicoStockData = getquimicoStock();
$bidonesStockData = getbidonesStock();
// Convierte los datos en formato JSON y los envía como respuesta
$response = [
    'quimicoStock' => $quimicoStockData,
    'bidonesStock' => $bidonesStockData
];

header('Content-Type: application/json');
echo json_encode($response);
?>
