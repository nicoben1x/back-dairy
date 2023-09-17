<?php

require 'vendor/autoload.php'; // Asegúrate de incluir la biblioteca para leer archivos Excel, como PhpSpreadsheet




require 'database.php'; // Incluye el archivo con la configuración de la conexión a la base de datos

// Función para insertar o actualizar datos en la base de datos
function insertOrUpdateDatos($productId, $code, $description, $presentation, $dealerPrice, $retailPrice, $conn) {
    try {
        // Verifica y ajusta los valores
        if ($code === "#N/A") {
            $code = "-";
        }

        if ($dealerPrice == 42 && $retailPrice == 42) {
            $dealerPrice = 0;
            $retailPrice = 0;
        }

        // Consulta si 'code' ya existe en la base de datos
        $stmt = $conn->prepare('SELECT * FROM productNormal WHERE code = ?');
        $stmt->execute([$code]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($row) {
            // Si 'code' existe, actualiza la fila existente
            $stmt = $conn->prepare('UPDATE productNormal SET dealerPrice = ?, retailPrice = ?, description = ?, presentation = ? WHERE code = ?');
            $stmt->execute([$dealerPrice, $retailPrice, $description, $presentation, $code]);

            echo "Registro actualizado para el código $code\n";
        } else {
            // Si 'code' no existe, inserta una nueva fila
            $stmt = $conn->prepare('INSERT INTO productNormal (productId, code, description, presentation, dealerPrice, retailPrice) VALUES (?, ?, ?, ?, ?, ?)');
            $stmt->execute([$productId, $code, $description, $presentation, $dealerPrice, $retailPrice]);

            echo "Nuevo registro insertado para el código $code\n";
        }
    } catch (PDOException $e) {
        echo "Error al insertar o actualizar datos en la base de datos: " . $e->getMessage() . "\n";
        echo $code . "\n";
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
        $dealerPrice = $worksheet->getCell('D' . $rowNum)->getValue();
        $retailPrice = $worksheet->getCell('E' . $rowNum)->getValue();

        if (!is_string($code)) {
            $code = strval($code);
        }

        if (!empty($code)) {
            insertOrUpdateDatos($productId, $code, $description, $presentation, $dealerPrice, $retailPrice, $conn);
            echo "$productId, $code, $description, $presentation, $dealerPrice, $retailPrice\n";
        } else {
            echo "Fila con 'code' vacío ignorada\n";
            echo "Fila $rowNum - Código: \"$code\"\n";
        }
    }
} catch (Exception $e) {
    echo "Error al procesar el archivo Excel: " . $e->getMessage() . "\n";
}
?>
