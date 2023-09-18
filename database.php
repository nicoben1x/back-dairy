<?php
$host = '45.77.158.192'; // Cambia esto al host de tu base de datos
$dbname = 'dairycom_productNormal'; // Cambia esto al nombre de tu base de datos
$username = 'dairycom_nuevvo'; // Cambia esto a tu nombre de usuario de la base de datos
$password = 'buenosaires159951'; // Cambia esto a tu contraseña de la base de datos

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Error en la conexión a la base de datos: " . $e->getMessage());
}
?>

