<?php
$servername = "localhost";
$username_db = "root";
$password_db = "";
$dbname = "peddidos";
session_start();
header('Content-Type: application/json'); // Asegurarse de que el contenido sea JSON

$response = ['success' => false, 'message' => ''];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    if (!isset($data['productId']) || !isset($data['quantity'])) {
        $response['message'] = 'Invalid input';
        echo json_encode($response);
        exit;
    }
    $productId = $data['productId'];
    $quantity = $data['quantity'];

    if (!isset($_SESSION['email'])) {
        $response['message'] = 'User not logged in';
        echo json_encode($response);
        exit;
    }

    $email = $_SESSION['email'];

    $conn = new mysqli($servername, $username_db, $password_db, $dbname);

    if ($conn->connect_error) {
        $response['message'] = "Connection failed: " . $conn->connect_error;
        echo json_encode($response);
        exit;
    }

    // Obtener el carrito del usuario
    $sql = "SELECT * FROM carrito WHERE email='$email'";
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $updated = false;

        // Verificar si el producto ya está en el carrito
        for ($i = 0; $i < 10; $i++) {
            if ($row["p$i"] == $productId) {
                $row["c$i"] += $quantity;
                $sqlUpdate = "UPDATE carrito SET c$i = " . $row["c$i"] . " WHERE email='$email'";
                if ($conn->query($sqlUpdate) === TRUE) {
                    $updated = true;
                }
                break;
            }
        }

        // Agregar el producto a una posición vacía en el carrito
        if (!$updated) {
            for ($i = 0; $i < 10; $i++) {
                if ($row["p$i"] == 0) {
                    $sqlInsert = "UPDATE carrito SET p$i = $productId, c$i = $quantity WHERE email='$email'";
                    if ($conn->query($sqlInsert) === TRUE) {
                        $updated = true;
                    }
                    break;
                }
            }
        }

        if ($updated) {
            $response['success'] = true;
        } else {
            $response['message'] = 'No se pudo actualizar el carrito';
        }
    } else {
        $response['message'] = 'No se encontró el carrito';
    }

    $conn->close();
    echo json_encode($response);
    exit;
} else {
    $response['message'] = 'Invalid request method';
    echo json_encode($response);
    exit;
}
?>
