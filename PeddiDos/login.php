<?php
session_start();
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "peddidos";

// Conectar a la base de datos
$conn = new mysqli($servername, $username, $password, $dbname);

// Verificar conexión
if ($conn->connect_error) {
    die("Conexión fallida: " . $conn->connect_error);
}

// Inicializar variables de mensaje de error y de valores de los inputs
$error_message = "";
$username_value = "";
$email_value = "";
$telefono_value = "";

// Función para obtener el contenido binario de una imagen
function getProfilePicture($filePath) {
    return file_get_contents($filePath);
}

// Registro de usuario
if (isset($_POST['register'])) {
    $username = $_POST['username'];
    $email = $_POST['email'];
    $password = password_hash($_POST['password'], PASSWORD_BCRYPT);
    $telefono = $_POST['telefono'];

    // Guardar valores en variables para mantenerlos en el formulario
    $username_value = $username;
    $email_value = $email;
    $telefono_value = $telefono;

    // Verificar si el correo electrónico tiene la terminación correcta
    if (!preg_match('/@cbtis19\.edu\.mx$/', $email)) {
        $error_message = "Solo se permite ingresar con el correo institucional.";
    } 
    // Verificar si el número de teléfono tiene al menos 10 dígitos
    elseif (!preg_match('/^\d{10,}$/', $telefono)) {
        $error_message = "El número de teléfono debe tener al menos 10 dígitos.";
    } 
    else {
        // Verificar si el correo electrónico ya existe
        $sql = "SELECT * FROM usuarios WHERE email='$email'";
        $result = $conn->query($sql);

        if ($result->num_rows > 0) {
            $error_message = "El correo electrónico ya está registrado.";
        } else {
            // Seleccionar una imagen aleatoria
            $images = ["images/gato1.png", "images/gato2.png", "images/gato3.png", "images/gato4.png", "images/gato5.png"];
            $randomIndex = array_rand($images);
            $randomImage = $images[$randomIndex];
            $profilePicture = getProfilePicture($randomImage);
            $profilePicture = $conn->real_escape_string($profilePicture);

            // Insertar en la tabla usuarios
            $sql = "INSERT INTO usuarios (username, email, password, telefono, categoria, profile_picture) 
                    VALUES ('$username', '$email', '$password', '$telefono', 'All', '$profilePicture')";
            if ($conn->query($sql) === TRUE) {
                // Insertar en la tabla carrito
                $sql = "INSERT INTO carrito (email) VALUES ('$email')";
                if ($conn->query($sql) === TRUE) {
                    $_SESSION['loggedin'] = true;
                    $_SESSION['username'] = $username;
                    header('Location: index.php');
                    exit;
                } else {
                    $error_message = "Error: " . $sql . "<br>" . $conn->error;
                }
            } else {
                $error_message = "Error: " . $sql . "<br>" . $conn->error;
            }
        }
    }
}

// Inicio de sesión de usuario
if (isset($_POST['login'])) {
    $email = $_POST['email'];
    $password = $_POST['password'];

    // Guardar valores en variables para mantenerlos en el formulario
    $email_value = $email;

    $sql = "SELECT * FROM usuarios WHERE email='$email'";
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        if (password_verify($password, $row['password'])) {
            $_SESSION['loggedin'] = true;
            $_SESSION['username'] = $row['username'];
            $_SESSION['email'] = $row['email'];
            header('Location: index.php');
            exit;
        } else {
            $error_message = "Contraseña incorrecta.";
        }
    } else {
        $error_message = "No existe una cuenta con ese correo electrónico.";
    }
}



$conn->close();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Login y Registro</title>
    <link rel="stylesheet" href="slogin.css">
</head>
<body>
    <div id="container">
        <?php
        if ($error_message) {
            echo "<p style='color: red;'>$error_message</p>";
        }
        ?>
        <form action="login.php" method="post">
            <h2>Iniciar Sesión</h2>
            <input type="email" name="email" placeholder="Correo electrónico" value="<?php echo htmlspecialchars($email_value); ?>" required>
            <input type="password" name="password" placeholder="Contraseña" required>
            <button type="submit" name="login">Iniciar Sesión</button>
        </form>
        <form action="login.php" method="post">
            <h2>Registrarse</h2>
            <input type="text" name="username" placeholder="Nombre de usuario" value="<?php echo htmlspecialchars($username_value); ?>" required>
            <input type="email" name="email" placeholder="Correo electrónico" value="<?php echo htmlspecialchars($email_value); ?>" required>
            <input type="password" name="password" placeholder="Contraseña" required>
            <input type="text" name="telefono" placeholder="Teléfono" value="<?php echo htmlspecialchars($telefono_value); ?>" required>
            <button type="submit" name="register">Registrarse</button>
        </form>
    </div>
    <script src="slogin.js"></script>
</body>
</html>