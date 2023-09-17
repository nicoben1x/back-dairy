<?php
require 'vendor/autoload.php'; // Asegúrate de incluir la biblioteca para leer archivos Excel, como PhpSpreadsheet
require 'database.php'; // Incluye el archivo con la configuración de la conexión a la base de datos

// Función para insertar o actualizar datos en la base de datos
function insertOrUpdateDatos($productId, $code, $description, $presentation, $dealerPrice, $retailPrice, $pdo, $rowNum) {
    try {
        // Verifica y ajusta los valores
        if ($code === "#N/A" || $code === "#REF!" || $code === "#VALUE!") {
            $code = "-";
        }

        // Verifica si dealerPrice es numérico, de lo contrario, establece 0
        $dealerPrice = is_numeric($dealerPrice) ? number_format($dealerPrice, 2, '.', '') : 0;

        // Verifica si retailPrice es numérico, de lo contrario, establece 0
        $retailPrice = is_numeric($retailPrice) ? number_format($retailPrice, 2, '.', '') : 0;

        // Consulta si 'code' ya existe en la base de datos
        $stmt = $pdo->prepare('SELECT * FROM productNormal WHERE code = ?');
        $stmt->execute([$code]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($row) {
            // Si 'code' existe, actualiza la fila existente
            $stmt = $pdo->prepare('UPDATE productNormal SET dealerPrice = ?, retailPrice = ?, description = ?, presentation = ? WHERE code = ?');
            $stmt->execute([$dealerPrice, $retailPrice, $description, $presentation, $code]);

            echo "Registro actualizado para el código $code\n";
            echo "Dealer Price: " . number_format($dealerPrice, 2) . "\n";
            echo "Retail Price: " . number_format($retailPrice, 2) . "\n";
        } else {
            // Si 'code' no existe, inserta una nueva fila
            $stmt = $pdo->prepare('INSERT INTO productNormal (productId, code, description, presentation, dealerPrice, retailPrice) VALUES (?, ?, ?, ?, ?, ?)');
            $stmt->execute([$productId, $code, $description, $presentation, $dealerPrice, $retailPrice]);

            echo "Nuevo registro insertado para el código $code\n";
            echo "Dealer Price: " . number_format($dealerPrice, 2) . "\n";
            echo "Retail Price: " . number_format($retailPrice, 2) . "\n";
        }
    } catch (PDOException $e) {
        // Registra el error y la información de la celda no procesada
        echo "Error al insertar o actualizar datos en la base de datos: " . $e->getMessage() . "\n";
        echo "Fila $rowNum - Código: \"$code\"\n";
        echo "Descripción: \"$description\"\n";
        echo "Presentación: \"$presentation\"\n";
        echo "Dealer Price: \"$dealerPrice\"\n";
        echo "Retail Price: \"$retailPrice\"\n";
    }
}

// Procesar el archivo Excel
try {
    $inputFileName = './archivo.xlsm'; // Reemplaza con la ruta de tu archivo Excel

    $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($inputFileName);
    $worksheet = $spreadsheet->getActiveSheet();

    for ($rowNum = 5; $rowNum <= 1311; $rowNum++) {
        $productId = null;
        $code = $worksheet->getCell('A' . $rowNum)->getValue();
        $description = $worksheet->getCell('B' . $rowNum)->getValue();
        $presentation = $worksheet->getCell('C' . $rowNum)->getValue();
        $dealerPrice = $worksheet->getCell('D' . $rowNum)->getCalculatedValue();
        $retailPrice = $worksheet->getCell('E' . $rowNum)->getCalculatedValue();

        if (!is_string($code)) {
            $code = strval($code);
        }

        if (!empty($code)) {
            insertOrUpdateDatos($productId, $code, $description, $presentation, $dealerPrice, $retailPrice, $pdo, $rowNum);
        } else {
            echo "Fila con 'code' vacío ignorada\n";
            echo "Fila $rowNum - Código: \"$code\"\n";
        }
    }
} catch (Exception $e) {
    echo "Error al procesar el archivo Excel: " . $e->getMessage() . "\n";
}
?>
