<?php
session_start();

// Verificar si el usuario ha iniciado sesión
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    echo json_encode(['success' => false, 'message' => 'No se ha iniciado sesión.']);
    exit;
}

$username = $_SESSION['username'];
$data = json_decode(file_get_contents('php://input'), true);
$category = $data['category'];

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

// Actualizar la categoría del usuario en la base de datos
$sql = "UPDATE usuarios SET categoria='$category' WHERE username='$username'";

if ($conn->query($sql) === TRUE) {
    echo json_encode(['success' => true, 'message' => 'Categoría actualizada correctamente.']);
} else {
    echo json_encode(['success' => false, 'message' => 'Error al actualizar la categoría: ' . $conn->error]);
}

$conn->close();
?>
