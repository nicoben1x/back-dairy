<?php
// Habilita CORS para permitir solicitudes desde https://new.dairy.com.ar y otras
$allowedOrigins = [
    "https://dairy.com.ar",
    "http://192.168.100.40:3000",
    "http://localhost:3000"
];

$origin = $_SERVER['HTTP_ORIGIN'];

if (in_array($origin, $allowedOrigins)) {
    header("Access-Control-Allow-Origin: $origin");
}

require 'database.php';

function getNoticiasItems() {
    global $pdo;

    try {
        $query = "SELECT * FROM noticiasItems";
        $statement = $pdo->prepare($query);
        $statement->execute();
        $noticiasItems = $statement->fetchAll(PDO::FETCH_ASSOC);

        return $noticiasItems;
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode(['message' => 'Something goes wrong (ItemLimpieza)']);
        exit;
    }
}

// Agregar noticia
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $titulo = $_POST['titulo'];
    $contenido = $_POST['contenido'];
    $fecha = $_POST['fecha'];

    if (!empty($_FILES['imagen']['name'])) {
        $imagen = $_FILES['imagen']['name'];
        $targetDir = 'imagenesnoticias/'; // Ruta en el servidor
        $targetFile = $targetDir . $imagen;

        // Comprobar si el archivo ya existe
        $counter = 1;
        while (file_exists($targetFile)) {
            // Generar un nuevo nombre de archivo único con un número incrementado
            $filenameParts = pathinfo($imagen);
            $newFilename = $filenameParts['filename'] . '_' . $counter . '.' . $filenameParts['extension'];
            $targetFile = $targetDir . $newFilename;
            $counter++;
        }

        // URL completa
        $fullImageUrl = 'https://normal.dairy.com.ar/' . $targetFile;

        move_uploaded_file($_FILES['imagen']['tmp_name'], $targetFile);
    } else {
        $fullImageUrl = ''; // URL completa por defecto si no se proporciona una nueva imagen
    }

    try {
        $query = "INSERT INTO noticiasItems (titulo, contenido, fecha, imagen) VALUES (:titulo, :contenido, :fecha, :imagen)";
        $statement = $pdo->prepare($query);
        $statement->bindParam(':titulo', $titulo);
        $statement->bindParam(':contenido', $contenido);
        $statement->bindParam(':fecha', $fecha);
        $statement->bindParam(':imagen', $fullImageUrl); // Almacena la URL completa
        $statement->execute();
        // Redirigir o mostrar un mensaje de éxito
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode(['message' => 'Error al agregar la noticia']);
        exit;
    }
}

// Editar noticia
if ($_SERVER['REQUEST_METHOD'] === 'PUT') {
    parse_str(file_get_contents("php://input"), $putData);
    $noticiaId = $putData['id'];
    $titulo = $putData['titulo'];
    $contenido = $putData['contenido'];
    $fecha = $putData['fecha'];
    
    // Obtén la noticia actual para verificar si hay una imagen existente
    $query = "SELECT imagen FROM noticiasItems WHERE id = :id";
    $statement = $pdo->prepare($query);
    $statement->bindParam(':id', $noticiaId);
    $statement->execute();
    $noticiaActual = $statement->fetch(PDO::FETCH_ASSOC);

    // Verifica si se proporciona una nueva imagen
    if (!empty($_FILES['imagen']['name'])) {
        $imagen = $_FILES['imagen']['name'];
        $targetDir = 'uploads/';
        $targetFile = $targetDir . $imagen;
        move_uploaded_file($_FILES['imagen']['tmp_name'], $targetFile);
        
        // Si hay una imagen anterior, elimínala
        if (!empty($noticiaActual['imagen'])) {
            unlink($targetDir . $noticiaActual['imagen']);
        }
    } else {
        $imagen = $noticiaActual['imagen']; // Conserva la imagen existente si no se proporciona una nueva
    }
    
    try {
        $query = "UPDATE noticiasItems SET titulo = :titulo, contenido = :contenido, fecha = :fecha, imagen = :imagen WHERE id = :id";
        $statement = $pdo->prepare($query);
        $statement->bindParam(':id', $noticiaId);
        $statement->bindParam(':titulo', $titulo);
        $statement->bindParam(':contenido', $contenido);
        $statement->bindParam(':fecha', $fecha);
        $statement->bindParam(':imagen', $imagen);
        $statement->execute();
        // Redirigir o mostrar un mensaje de éxito
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode(['message' => 'Error al editar la noticia']);
        exit;
    }
}

// Eliminar noticia
if ($_SERVER['REQUEST_METHOD'] === 'DELETE') {
    $noticiaId = $_GET['id'];
    
    try {
        // Obtén la imagen de la noticia para eliminarla
        $query = "SELECT imagen FROM noticiasItems WHERE id = :id";
        $statement = $pdo->prepare($query);
        $statement->bindParam(':id', $noticiaId);
        $statement->execute();
        $noticia = $statement->fetch(PDO::FETCH_ASSOC);
        
        // Si hay una imagen asociada, elimínala
        if (!empty($noticia['imagen'])) {
            unlink('uploads/' . $noticia['imagen']);
        }

        $query = "DELETE FROM noticiasItems WHERE id = :id";
        $statement = $pdo->prepare($query);
        $statement->bindParam(':id', $noticiaId);
        $statement->execute();
        // Redirigir o mostrar un mensaje de éxito
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode(['message' => 'Error al eliminar la noticia']);
        exit;
    }
}


// Obtener noticia por ID
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['id'])) {
    $noticiaId = $_GET['id'];
    
    try {
        $query = "SELECT * FROM noticiasItems WHERE id = :id";
        $statement = $pdo->prepare($query);
        $statement->bindParam(':id', $noticiaId);
        $statement->execute();
        $noticia = $statement->fetch(PDO::FETCH_ASSOC);
        
        if ($noticia) {
            // Noticia encontrada, envía los datos de la noticia como respuesta
         
            $response2 = [
                'noticia' => $noticia
            ];
            
            header('Content-Type: application/json');
            echo json_encode($response2);
            echo exit;
           
        } else {
            // Noticia no encontrada
            http_response_code(404);
            echo json_encode(['message' => 'Noticia no encontrada']);
        }
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode(['message' => 'Error al obtener la noticia']);
    }
}


// Obtén los datos de ItemLimpieza
$noticiasItemsData = getNoticiasItems();

$response = [
    'noticiasItems' => $noticiasItemsData
];

header('Content-Type: application/json');
echo json_encode($response);
?>
