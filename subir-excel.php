<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Allow-Headers: Content-Type");
header("Access-Control-Allow-Credentials: true");
header("Content-Type: application/json");

$response = array(); // Array para almacenar la respuestaa

if ($_FILES['file']['error'] === UPLOAD_ERR_OK) {
    $nombre_archivo = 'archivo';
    $carpeta_destino = 'excel/';
    $ruta_archivo = $carpeta_destino . $nombre_archivo . '.xlsm';

    // Mueve el archivo al directorio de destino
    if (move_uploaded_file($_FILES['file']['tmp_name'], $ruta_archivo)) {
        $response['status'] = 'success';
        $response['message'] = 'Archivo subido correctamente.';
    } else {
        $response['status'] = 'error';
        $response['message'] = 'Error al mover el archivo.';
    }
} else {
    $response['status'] = 'error';
    $response['message'] = 'Error al subir el archivo.';
}

// Devuelve la respuesta en formato JSON
echo json_encode($response);
?>

