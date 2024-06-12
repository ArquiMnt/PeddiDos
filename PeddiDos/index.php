<?php
session_start();
// Verificar si el usuario ha iniciado sesión
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header('Location: login.php');
    exit;
}

// Obtener los datos del usuario desde la sesión
$username = $_SESSION['username'];
$email = $_SESSION['email'];

// Conectar a la base de datos para obtener el teléfono y foto de perfil del usuario
$servername = "localhost";
$username_db = "root";
$password_db = "";
$dbname = "peddidos";

$conn = new mysqli($servername, $username_db, $password_db, $dbname);

// Verificar conexión
if ($conn->connect_error) {
    die("Conexión fallida: " . $conn->connect_error);
}

$sql = "SELECT telefono, profile_picture, admin FROM usuarios WHERE email='$email'";
$result = $conn->query($sql);

if ($result->num_rows > 0) {
    $row = $result->fetch_assoc();
    $telefono = $row['telefono'];
    $profile_picture = $row['profile_picture'];
    $isAdmin = $row['admin'] == 1 ? true : false;
} else {
    // Si no se encuentran los datos del usuario, destruir la sesión y redirigir a login
    session_destroy();
    header('Location: login.php');
    exit;
}

// Manejo de la eliminación de cuenta
if (isset($_POST['delete_account'])) {
    $conn->autocommit(FALSE); // Iniciar transacción

    $sql1 = "DELETE FROM usuarios WHERE email='$email'";
    $sql2 = "DELETE FROM carrito WHERE email='$email'";

    $success1 = $conn->query($sql1);
    $success2 = $conn->query($sql2);

    if ($success1 && $success2) {
        $conn->commit(); // Confirmar transacción
        session_destroy();
        header('Location: login.php');
        exit;
    } else {
        $conn->rollback(); // Revertir transacción
        echo "Error al eliminar la cuenta: " . $conn->error;
    }

    $conn->autocommit(TRUE); // Finalizar transacción
}

// Manejo del cierre de sesión
if (isset($_POST['logout'])) {
    session_destroy();
    header('Location: login.php');
    exit;
}

// Manejo de la eliminación de productos del carrito
if (isset($_POST['eliminar_producto'])) {
    $product_number = $_POST['product_number'];

    // Construir los nombres de las columnas a eliminar
    $product_column = "p$product_number";
    $quantity_column = "c$product_number";

    // Eliminar el producto del carrito
    $delete_query = "UPDATE carrito SET $product_column = 0, $quantity_column = 0 WHERE email='$email'";
    if ($conn->query($delete_query) === TRUE) {
        // Producto eliminado con éxito
        header('Location: index.php'); // Redirigir a la misma página para evitar reenvío de formulario
        exit;
    } else {
        // Error al eliminar el producto
        echo "Error al eliminar el producto del carrito: " . $conn->error;
    }
}

// Manejo de la eliminación de productos
if (isset($_POST['delete_product'])) {
    $productID = $_POST['product_id'];
    $sql = "DELETE FROM food WHERE ID='$productID'";
    if ($conn->query($sql) === TRUE) {
        header('Location: index.php');
        exit;
    } else {
        echo "Error al eliminar el producto: " . $conn->error;
    }
}

?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>PeddiDos</title>
    <link href="https://unpkg.com/cropperjs/dist/cropper.css" rel="stylesheet">
    <link rel="stylesheet" href="menufood.css">
    <link rel="stylesheet" href="menuperfil.css">
    <link rel="stylesheet" href="menupicture.css">
    <link rel="stylesheet" href="products.css">
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="stylemenu.css">
    <link rel="stylesheet" href="carrito.css">
    <link rel="stylesheet" href="styleshop.css">
</head>

<body>
<div id="shop-panel">
    <div class="content-shop">
        <div class="product-shop">
        <?php
        // Consulta SQL para obtener los productos del carrito
        $sql = "SELECT * FROM carrito WHERE email='$email'";
        $result = $conn->query($sql);

        $total_price_all = 0; // Inicializar el total acumulado

        if ($result->num_rows > 0) {
            // Imprimir los datos de cada fila
            while ($row = $result->fetch_assoc()) {
                // Recuperar el id del producto y la cantidad del carrito
                for ($i = 0; $i < 10; $i++) {
                    $product_id = $row["p$i"];
                    $quantity = $row["c$i"];

                    // Si la cantidad es mayor que 0, mostrar el producto
                    if ($quantity > 0) {
                        // Consultar los datos del producto en la tabla 'food'
                        $product_query = "SELECT * FROM food WHERE ID='$product_id'";
                        $product_result = $conn->query($product_query);

                        // Si se encuentra el producto, imprimirlo
                        if ($product_result->num_rows > 0) {
                            $product_data = $product_result->fetch_assoc();
                            $total_price = $product_data['Precio'] * $quantity;

                            // Sumar el total del producto al total acumulado
                            $total_price_all += $total_price;

                            // Imprimir el producto con los datos recuperados
                            echo '<div class="productcart" data-id="' . $product_data['ID'] . '">';
                            echo '<div class="product-photo">';
                            echo '<img src="data:image/png;base64,' . base64_encode($product_data['Picture']) . '" alt="Imagen del producto">';
                            echo '</div>';
                            echo '<div class="product-details">';
                            echo '<div class="product-info">';
                            echo '<p><strong>Nombre:</strong> ' . $product_data['FoodName'] . '</p>';
                            echo '<p><strong>Categoría:</strong> ' . $product_data['Categoria'] . '</p>';
                            echo '<p><strong>Precio:</strong> $' . number_format($product_data['Precio'], 2) . '</p>';
                            echo '</div>';
                            echo '<div class="product-butons-cart">';
                            echo    '<div class="cant">';
                            echo        '<p class="cartctext">' . $quantity . '</p>';
                            echo    '</div>';
                            echo    '<form method="POST" action="index.php">';
                            echo        '<input type="hidden" name="product_number" value="' . $i . '">';
                            echo        '<button type="submit" name="eliminar_producto" class="EliPro">Eliminar</button>';
                            echo    '</form>';
                            echo '<p><strong>Precio: </strong>$' . $total_price . '</p>';
                            echo '</div>';
                            echo '</div>';
                            echo '</div>';
                        } else {
                            echo "Producto no encontrado";
                        }
                    }
                }
            }
        } else {
            echo "El carrito está vacío";
        }

// Realizar la consulta para obtener las columnas p0, p1, ..., p9 de la tabla carrito
$sql_get_products = "SELECT ";
for ($i = 0; $i < 10; $i++) {
    $sql_get_products .= "p$i, ";
}
$sql_get_products = rtrim($sql_get_products, ", ") . " FROM carrito WHERE email='$email'";

// Ejecutar la consulta
$result_get_products = $conn->query($sql_get_products);

// Contar cuántas columnas tienen valores diferentes de 0
$total_products = 0;
if ($result_get_products->num_rows > 0) {
    $row = $result_get_products->fetch_assoc();
    for ($i = 0; $i < 10; $i++) {
        if ($row["p$i"] != 0) {
            $total_products++;
        }
    }
} else {
    $total_products = 0; // Por si no se encuentra ningún producto en el carrito
}

// Determinar la visibilidad del div
$div_visibility = $total_products > 0 ? 'visible' : 'hidden';

        $conn->close();
        ?>
        </div>
        <button class="buy">Comprar carrito</button>
        <div class="ctotalprice">
            <p><strong>PRECIO TOTAL:</strong></p>
            <p class="totalprice"><?php echo '$' . number_format($total_price_all, 2); ?></p>
        </div>
    </div>
</div>

    <header>
        <button id="menu-btn">☰</button>
        <?php if (!$isAdmin): ?>
            <button id="carshop-btn">
                <img src="https://cdn-icons-png.flaticon.com/512/4/4295.png" id="carshopicon">
            </button>
            <div class="CantPro" style="visibility: <?php echo $div_visibility; ?>;">
                <p class="CantidadPrductosCarrito"><strong><?php echo $total_products; ?></strong></p>
            </div>
        <?php endif; ?>
        <?php if ($isAdmin): ?>
            <button id="agree-btn">+</button>
        <?php endif; ?>
    </header>
    <div id="menu-panel">
        <div id="perfilcontainer">
            <div id="pc">
                <img id="ImagePerfil" src="data:image/jpeg;base64,<?php echo base64_encode($profile_picture); ?>" alt="Imagen de perfil">
                <button id="Perfilbutton">
                <img src="https://cdn.iconscout.com/icon/free/png-256/free-camera-1831-475002.png" id="cameraicon">
                </button>
            </div>
            <input type="file" id="imagePerfil" accept="image/*" style="display: none;" />
        </div>
        <div id="AutoPerfilText">
            <p><strong>Fotos de perfil:</strong></p>
        </div>
        <div class="AutoPerfilButton">
            <button data-image-url="images/gato1.png" class="gatitosbutton">
                <img src="images/gato1.png" class="gatitosperfil">
            </button>
            <button data-image-url="images/gato2.png" class="gatitosbutton">
                <img src="images/gato2.png" class="gatitosperfil">
            </button>
            <button data-image-url="images/gato3.png" class="gatitosbutton">
                <img src="images/gato3.png" class="gatitosperfil">
            </button>
            <button data-image-url="images/gato4.png" class="gatitosbutton">
                <img src="images/gato4.png" class="gatitosperfil">
            </button>
            <button data-image-url="images/gato5.png" class="gatitosbutton">
                <img src="images/gato5.png" class="gatitosperfil">
            </button>
        </div>
        <div id="PerfilText">
            <p><strong>Usuario:</strong> <?php echo htmlspecialchars($username); ?></p>
            <p><strong>Correo:</strong> <?php echo htmlspecialchars($email); ?></p>
            <p><strong>Teléfono:</strong> <?php echo htmlspecialchars($telefono); ?></p>
        </div>
            <form action="index.php" method="post" class="accountbuttons">
                <button type="submit" name="logout" class="btnaccount" id="btnlogout">Cerrar Sesión</button>
                <button type="submit" name="delete_account" class="btnaccount" id="btndelete_account">Eliminar Cuenta</button>
            </form>
    </div>

    <div id="agree-panel">
        <div id="form-container">
            <div class="bordered-div" id="resultContainer">
                <img id="previewImage" src="" alt="Previsualización de la imagen recortada">
                <button class="boton-seleccionar" id="selectButton">
                    <img src="https://cdn.iconscout.com/icon/free/png-256/free-camera-1831-475002.png" id="cameraiconfood">
                </button>
            </div>
            <input type="file" id="imageInput" accept="image/*" style="display: none;" />
        </div>
        <form id="foodForm" method="POST">
            <p>NOMBRE DEL PRODUCTO</p>
            <input type="text" name="FoodName" placeholder="Nombre" class="rinput">
            <p>CATEGORIA</p>
            <select name="Categoria" class="rinput">
                <option value="Refrigerador">Refrigerador</option>
                <option value="Dulcería">Dulcería</option>
                <option value="Estanteria">Estanteria</option>
                <option value="Comida">Comida</option>
            </select>
            <p>PRECIO</p>
            <input type="number" name="Precio" placeholder="Precio" class="rinput">
            <button class="boton-guardar" id="saveButton">Guardar</button>
        </form>
    </div>
    <form method="POST" class="form">
    <div class="input-group">
        <input type="text" name="searcher" id="searcher" placeholder="Buscar producto" class="rounded-input">
        <button type="submit" name="search-tbn" class="button">
            <img src="https://cdn.icon-icons.com/icons2/2024/PNG/512/searcher_magnifyng_glass_search_locate_find_icon_123813.png" id="searchicon">
        </button>
    </div>
</form>
<div class="Categories">
    <button class="filter-button" data-category="Refrigerador">Refrigerador</button>
    <button class="filter-button" data-category="Dulceria">Dulcería</button>
    <button class="filter-button" data-category="Estanteria">Estantería</button>
    <button class="filter-button" data-category="Comida">Comida</button>
    <button class="filter-button" data-category="All">All</button>
</div>
    <div class="menu-flotante" id="menuFlotante">
        <div class="image-container">
            <img id="image" src="" alt="Imagen seleccionada">
        </div>
        <button class="boton-recortar" id="cropButton">Recortar Imagen</button>
    </div>

    <div class="menu-flotante-perfil" id="menuFlotante-perfil">
        <div class="image-container-perfil">
            <img id="image-perfil" src="" alt="Imagen seleccionada-perfil">
        </div>
        <button class="boton-recortar-perfil" id="cropButton-perfil">Recortar Imagen</button>
    </div>

    <?php
    include("products.php");
    ?>

    <!-- Pasar variable PHP a JavaScript -->
    <script>
        var isAdmin = <?php echo json_encode($isAdmin); ?>;
    </script>
    <script src="https://unpkg.com/cropperjs"></script>
    <script src="scriptpicture.js"></script>
    <script src="scriptperfil.js"></script>
    <script src="script.js"></script>
    <script src="carrito.js"></script>
</body>


</html>
