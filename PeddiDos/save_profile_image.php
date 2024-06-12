<?php
session_start();

// Verificar si el usuario ha iniciado sesión
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    echo json_encode(array('success' => false, 'error' => 'No hay usuario logueado.'));
    exit;
}

// Obtener el nombre de usuario
$username = $_SESSION['username'];

// Verificar si se recibió la imagen de perfil recortada
if (!isset($_POST['imagePerfil'])) {
    echo json_encode(array('success' => false, 'error' => 'No se recibió ninguna imagen de perfil.'));
    exit;
}

// Decodificar la imagen de perfil en base64
$base64_image = $_POST['imagePerfil'];
$binary_image = base64_decode(preg_replace('#^data:image/\w+;base64,#i', '', $base64_image));

// Conectar a la base de datos
$servername = "localhost";
$username_db = "root";
$password_db = "";
$dbname = "peddidos";

$conn = new mysqli($servername, $username_db, $password_db, $dbname);

// Verificar conexión
if ($conn->connect_error) {
    echo json_encode(array('success' => false, 'error' => 'Error de conexión a la base de datos: ' . $conn->connect_error));
    exit;
}

// Preparar la consulta SQL para actualizar la imagen de perfil en la base de datos
$sql = "UPDATE usuarios SET profile_picture = ? WHERE username = ?";
$stmt = $conn->prepare($sql);

// Verificar si la preparación de la consulta SQL fue exitosa
if ($stmt === false) {
    echo json_encode(array('success' => false, 'error' => 'Error al preparar la consulta SQL: ' . $conn->error));
    exit;
}

// Vincular los parámetros a la declaración preparada
$stmt->bind_param('ss', $binary_image, $username);

// Ejecutar la declaración preparada
if ($stmt->execute()) {
    echo json_encode(array('success' => true));
} else {
    echo json_encode(array('success' => false, 'error' => 'Error al guardar la imagen de perfil en la base de datos: ' . $stmt->error));
}

// Cerrar la conexión
$stmt->close();
$conn->close();
?>
