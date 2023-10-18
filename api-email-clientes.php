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

function getEmailClientes() {
    global $pdo; // $pdo debe ser la instancia de tu conexión PDO

    try {
        $query = "SELECT * FROM ClientesEmail";
        $statement = $pdo->prepare($query);
        $statement->execute();
        $EmailClientes = $statement->fetchAll(PDO::FETCH_ASSOC);

        return $EmailClientes;
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode(['message' => 'Something goes wrong (ItemLimpieza)']);
        exit; // Termina la ejecución del script si hay un error
    }
}

// Obtén los datos de ItemLimpieza
$EmailClientesData = getEmailClientes();

// Convierte los datos en formato JSON y los envía como respuesta
$response = [
    'EmailClientes' => $EmailClientesData
];

header('Content-Type: application/json');
echo json_encode($response);
?>
