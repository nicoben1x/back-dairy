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

// Ruta al archivo PDF que deseas servirr
$pdfFilePath = './libro.pdf';

// Asegúrate de establecer los encabezados MIME adecuados para el PDF
header('Content-type: application/pdf');
header('Content-Disposition: inline; filename="libro.pdf"');
header('Content-Transfer-Encoding: binary');
header('Accept-Ranges: bytes');

// Lee el archivo y envíalo como respuesta
readfile($pdfFilePath);
?>
