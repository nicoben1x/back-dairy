<?php
require 'vendor/autoload.php'; // Asegúrate de incluir la biblioteca para leer archivos Excel, como PhpSpreadsheet
require 'database.php'; // Incluye el archivo con la configuración de la conexión a la base de datos

// Función para insertar o actualizar datos en la base de datos (primera versión)
function insertOrUpdateDatos($quimicoId, $code, $description, $presentation, $dealerPrice, $retailPrice, $costoKilo, $pdo, $rowNum) {
    try {
        // Verifica y ajusta los valores
        if ($code === "#N/A" || $code === "#REF!" || $code === "#VALUE!") {
            $code = "-";
        }

        // Verifica si dealerPrice es numérico, de lo contrario, establece 0
        $dealerPrice = is_numeric($dealerPrice) ? number_format($dealerPrice, 2, '.', '') : 0;

        // Verifica si retailPrice es numérico, de lo contrario, establece 0
        $retailPrice = is_numeric($retailPrice) ? number_format($retailPrice, 2, '.', '') : 0;

        // Verifica si costoKilo es numérico, de lo contrario, establece 0
        $costoKilo = is_numeric($costoKilo) ? number_format($costoKilo, 2, '.', '') : 0;

        // Consulta si 'code' ya existe en la base de datos
        $stmt = $pdo->prepare('SELECT * FROM quimicoNormal WHERE code = ?');
        $stmt->execute([$code]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($row) {
            // Si 'code' existe, actualiza la fila existente
            $stmt = $pdo->prepare('UPDATE quimicoNormal SET dealerPrice = ?, retailPrice = ?, description = ?, presentation = ?, costoKilo = ? WHERE code = ?');
            $stmt->execute([$dealerPrice, $retailPrice, $description, $presentation, $costoKilo, $code]);

            echo "Registro actualizado para el código $code\n";
            echo "Dealer Price: " . number_format($dealerPrice, 2) . "\n";
            echo "Retail Price: " . number_format($retailPrice, 2) . "\n";
            echo "Costo por Kilo: " . number_format($costoKilo, 2) . "\n";
        } else {
            // Si 'code' no existe, inserta una nueva fila
            $stmt = $pdo->prepare('INSERT INTO quimicoNormal (quimicoId, code, description, presentation, dealerPrice, retailPrice, costoKilo) VALUES (?, ?, ?, ?, ?, ?, ?)');
            $stmt->execute([$quimicoId, $code, $description, $presentation, $dealerPrice, $retailPrice, $costoKilo]);

            echo "Nuevo registro insertado para el código $code\n";
            echo "Dealer Price: " . number_format($dealerPrice, 2) . "\n";
            echo "Retail Price: " . number_format($retailPrice, 2) . "\n";
            echo "Costo por Kilo: " . number_format($costoKilo, 2) . "\n";
        }
    } catch (PDOException $e) {
        // Registra el error y la información de la celda no procesada
        echo "Error al insertar o actualizar datos en la base de datos: " . $e->getMessage() . "\n";
        echo "Fila $rowNum - Código: \"$code\"\n";
        echo "Descripción: \"$description\"\n";
        echo "Presentación: \"$presentation\"\n";
        echo "Dealer Price: \"$dealerPrice\"\n";
        echo "Retail Price: \"$retailPrice\"\n";
        echo "Costo por Kilo: \"$costoKilo\"\n";
    }
}

// Función para insertar o actualizar datos en la base de datos (segunda versión)
function insertOrUpdateDatos2($productId, $code, $description, $presentation, $dealerPrice, $retailPrice, $pdo, $rowNum) {
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

// Procesar el archivo Excel (primera versión)
try {
    $inputFileName = './archivo.xlsm'; // Reemplaza con la ruta de tu archivo Excel
    $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($inputFileName);
    $worksheet = $spreadsheet->getSheetByName('Quimicos');

    for ($rowNum = 7; $rowNum <= 101; $rowNum++) {
        $quimicoId = null; // Asegúrate de obtener el valor correcto para quimicoId
        $code = $worksheet->getCell('A' . $rowNum)->getValue();
        $description = $worksheet->getCell('B' . $rowNum)->getValue();
        $presentation = $worksheet->getCell('C' . $rowNum)->getValue();
        $dealerPrice = $worksheet->getCell('D' . $rowNum)->getCalculatedValue();
        $retailPrice = $worksheet->getCell('E' . $rowNum)->getCalculatedValue();
        $costoKilo = $worksheet->getCell('F' . $rowNum)->getCalculatedValue();
        if (!is_string($code)) {
            $code = strval($code);
        }
        if (!empty($code)) {
            insertOrUpdateDatos($quimicoId, $code, $description, $presentation, $dealerPrice, $retailPrice, $costoKilo, $pdo, $rowNum);
        } else {
            echo "Fila con 'code' vacío ignorada\n";
            echo "Fila $rowNum - Código: \"$code\"\n";
        }
    }
} catch (Exception $e) {
    echo "Error al procesar el archivo Excel: " . $e->getMessage() . "\n";
}

// Procesar el archivo Excel (segunda versión)
try {
    $inputFileName = './archivo.xlsm'; // Reemplaza con la ruta de tu archivo Excel
    $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($inputFileName);
    $worksheet = $spreadsheet->getSheetByName('Base');

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
            insertOrUpdateDatos2($productId, $code, $description, $presentation, $dealerPrice, $retailPrice, $pdo, $rowNum);
        } else {
            echo "Fila con 'code' vacío ignorada\n";
            echo "Fila $rowNum - Código: \"$code\"\n";
        }
    }
} catch (Exception $e) {
    echo "Error al procesar el archivo Excel: " . $e->getMessage() . "\n";
}
?>
