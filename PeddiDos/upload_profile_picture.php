<?php
session_start();

// Verificar si el usuario ha iniciado sesión
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    echo json_encode(['success' => false, 'message' => 'No se ha iniciado sesión.']);
    exit;
}

$username = $_SESSION['username'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_FILES['croppedImage']) && $_FILES['croppedImage']['error'] === UPLOAD_ERR_OK) {
        // Conectar a la base de datos
        $servername = "localhost";
        $username_db = "root";
        $password_db = "";
        $dbname = "peddidos";

        $conn = new mysqli($servername, $username_db, $password_db, $dbname);

        // Verificar conexión
        if ($conn->connect_error) {
            echo json_encode(['success' => false, 'message' => 'Conexión fallida: ' . $conn->connect_error]);
            exit;
        }

        // Obtener los datos del archivo
        $image = $_FILES['croppedImage']['tmp_name'];
        $imgContent = addslashes(file_get_contents($image));

        // Actualizar la imagen de perfil en la base de datos
        $sql = "UPDATE usuarios SET profile_picture='$imgContent' WHERE username='$username'";

        if ($conn->query($sql) === TRUE) {
            echo json_encode(['success' => true, 'imageUrl' => "data:image/jpeg;base64," . base64_encode($imgContent)]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Error al actualizar la imagen de perfil: ' . $conn->error]);
        }

        $conn->close();
    } else {
        echo json_encode(['success' => false, 'message' => 'No se recibió ninguna imagen o hubo un error al subir la imagen.']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Método de solicitud no válido.']);
}
?>