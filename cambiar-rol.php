<?php
// Importar la configuración de la base de datos desde database.php
require 'database.php';

// Verificar si se recibieron los parámetros necesarios en la URL
if (isset($_GET['user_id']) && isset($_GET['action']) && $_GET['action'] === 'cambiar_rol') {
    // Obtener el ID de usuario y realizar la actualización del rol
    $user_id = $_GET['user_id'];

    // Actualizar el rol del usuario a 'Cliente2'
    $sql_update = "UPDATE usuarios SET rol = 'Cliente' WHERE id = ?";
    try {
        $stmt_update = $pdo->prepare($sql_update);
        $stmt_update->execute([$user_id]);
        echo "El usuario ha sido habilitado exitosamente.";
    } catch (PDOException $e) {
        echo "Error al habilitar el usuario: " . $e->getMessage();
    }
} else {
    echo "Parámetros incorrectos o acción no permitida.";
}
?>
