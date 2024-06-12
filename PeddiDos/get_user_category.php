<?php
session_start();

// Verificar si el usuario ha iniciado sesión
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    echo json_encode(['success' => false, 'message' => 'No se ha iniciado sesión.']);
    exit;
}

$username = $_SESSION['username'];

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

// Obtener la categoría del usuario desde la base de datos
$sql = "SELECT categoria FROM usuarios WHERE username='$username'";
$result = $conn->query($sql);

if ($result->num_rows > 0) {
    $row = $result->fetch_assoc();
    echo json_encode(['success' => true, 'category' => $row['categoria']]);
} else {
    echo json_encode(['success' => false, 'message' => 'Usuario no encontrado.']);
}

$conn->close();
?>
