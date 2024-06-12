<?php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "peddidos";

// Crear conexión
$conn = new mysqli($servername, $username, $password, $dbname);

// Verificar conexión
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Obtener datos de la solicitud
$foodName = $_POST['FoodName'];
$categoria = $_POST['Categoria'];
$precio = $_POST['Precio'];
$imageData = $_POST['image'];

if ($foodName && $categoria && $precio && $imageData) {
    // Decodificar la imagen de Base64
    $imageData = str_replace('data:image/png;base64,', '', $imageData);
    $imageData = str_replace(' ', '+', $imageData);
    $imageContent = base64_decode($imageData);

    // Insertar la imagen y los datos en la base de datos
    $stmt = $conn->prepare("INSERT INTO food (FoodName, Categoria, Precio, Picture) VALUES (?, ?, ?, ?)");
    $null = NULL; // Necesario para send_long_data
    $stmt->bind_param("sssb", $foodName, $categoria, $precio, $null);
    $stmt->send_long_data(3, $imageContent);

    $response = [];
    if ($stmt->execute()) {
        $response['success'] = true;
    } else {
        $response['success'] = false;
        $response['error'] = $stmt->error;
    }

    $stmt->close();
} else {
    $response = ['success' => false, 'error' => 'Datos incompletos'];
}

$conn->close();

// Enviar respuesta
header('Content-Type: application/json');
echo json_encode($response);
?>
