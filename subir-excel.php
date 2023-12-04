<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Allow-Headers: Content-Type");
header("Access-Control-Allow-Credentials: true");
header("Content-Type: application/json");

function uploadAndReplaceFile($file) {
    $targetDir = __DIR__ . "/"; // Directorio donde se ejecuta el script
    $targetFileName = "archivo.xlsm"; // Nombre fijo del archivo
    $targetFile = $targetDir . $targetFileName;
    $uploadOk = 1;
    $fileType = strtolower(pathinfo($targetFile, PATHINFO_EXTENSION));

    // Permitir ciertos formatos de archivo (en este caso, solo XLMS)
    if ($fileType !== "xlsm") {
        echo json_encode(array("message" => "Solo se permite el formato de archivo XLMS."));
        $uploadOk = 0;
    }

    // Si el archivo ya existe, intenta reemplazarloo
    if (file_exists($targetFile)) {
        if (!unlink($targetFile)) {
            echo json_encode(array("message" => "Hubo un error al reemplazar el archivo existente."));
            $uploadOk = 0;
        }
    }

    // Verificar si $uploadOk está configurado en 0 por algún error
    if ($uploadOk == 0) {
        echo json_encode(array("message" => "El archivo no fue subido."));
    } else {
        // Intentar subir el archivo
        if (move_uploaded_file($file["tmp_name"], $targetFile)) {
            echo json_encode(array("message" => "El archivo se ha subido y reemplazado con el nombre 'archivo.xlsm'."));
        } else {
            echo json_encode(array("message" => "Hubo un error al subir el archivo."));
        }
    }
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_FILES["file"])) {
    uploadAndReplaceFile($_FILES["file"]);
} else {
    echo json_encode(array("message" => "No se recibió ningún archivo."));
}
?>

