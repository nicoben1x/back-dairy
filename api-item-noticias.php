<?php
// Habilita CORS para permitir solicitudes desde https://new.dairy.com.ar y otras
$allowedOrigins = [
    "https://dairy.com.ar",
    "http://192.168.100.40:3000",
    "http://localhost:3000",
    "https://new.dairy.com.ar" // Agrega el dominio correcto aquí
];

$origin = $_SERVER['HTTP_ORIGIN'];

if (in_array($origin, $allowedOrigins)) {
    header("Access-Control-Allow-Origin: $origin");
    header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE");
    header("Access-Control-Allow-Headers: Content-Type");
}

require 'database.php';

// Manejar solicitudes OPTIONS
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    header("Access-Control-Allow-Origin: $origin");
    header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE");
    header("Access-Control-Allow-Headers: Content-Type");
    exit;
}


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

    // Recupera los datos de la solicitud PUT
    $putData = file_get_contents("php://input");
    $data = json_decode($putData, true);

    if (isset($data['id'])) {
        $noticiaId = $data['id'];
    }

    if (isset($data['titulo'])) {
        $titulo = $data['titulo'];
    }

    if (isset($data['contenido'])) {
        $contenido = $data['contenido'];
    }

    if (isset($data['fecha'])) {
        $fecha = $data['fecha'];
    }

    // Verifica si se proporciona una nueva imagen
    $imageUpdate = $data['imagen'] !== "null";

    // Si se proporciona una nueva imagen, guarda la imagen en el servidor
    if ($imageUpdate) {
        // La imagen se envía como base64
        $imagen = $data['imagen'];
        $targetDir = 'imagenesnoticias/'; // Ruta en el servidor
        $targetFile = $targetDir . uniqid() . '.png'; // O ajusta la extensión según tu necesidad

        // Decodifica la imagen y la guarda en el servidor
        $imageData = base64_decode(preg_replace('#^data:image/\w+;base64,#i', '', $imagen));
        file_put_contents($targetFile, $imageData);

        // URL completa
        $fullImageUrl = 'https://normal.dairy.com.ar/' . $targetFile;
    } else {
        $fullImageUrl = ''; // URL completa por defecto si no se proporciona una nueva imagen
    }

    try {
        // Build the SQL query based on whether a new image is provided
        if ($imageUpdate) {
            $query = "UPDATE noticiasItems SET titulo = :titulo, contenido = :contenido, fecha = :fecha, imagen = :imagen WHERE id = :id";
        } else {
            $query = "UPDATE noticiasItems SET titulo = :titulo, contenido = :contenido, fecha = :fecha WHERE id = :id";
        }

        $statement = $pdo->prepare($query);
        $statement->bindParam(':id', $noticiaId);
        $statement->bindParam(':titulo', $titulo);
        $statement->bindParam(':contenido', $contenido);
        $statement->bindParam(':fecha', $fecha);

        // Bind the image parameter only if a new image is provided
        if ($imageUpdate) {
            $statement->bindParam(':imagen', $fullImageUrl);
        }

        $statement->execute();

        // Redirigir o mostrar un mensaje de éxito
        echo json_encode(['success' => true, 'message' => 'Noticia actualizada con éxito']);
        var_dump($titulo);
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode(['message' => 'Error al editar la noticia']);
        exit;
    }
}




// Eliminar noticia
if ($_SERVER['REQUEST_METHOD'] === 'DELETE') {
    $noticiaId = $_GET['id'];
    $noticiaImg = $_GET['imagen'];
    
    try {
        // Obtén la imagen de la noticia para eliminarla
        $query = "SELECT imagen FROM noticiasItems WHERE id = :id";
        $statement = $pdo->prepare($query);
        $statement->bindParam(':id', $noticiaId);
        $statement->execute();
        $noticia = $statement->fetch(PDO::FETCH_ASSOC);
        
        // Si hay una imagen asociada, elimínala
        if (!empty($noticia['imagen'])) {

            $nombreArchivo = basename($noticia['imagen']);
            unlink('imagenesnoticias/' . $nombreArchivo);
        
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
