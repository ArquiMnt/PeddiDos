<div class="main-container" id="main-container">
        <?php
        $conn = new mysqli($servername, $username_db, $password_db, $dbname);

        // Verificar conexión
        if ($conn->connect_error) {
            die("Connection failed: " . $conn->connect_error);
        }

        // Consulta SQL para obtener los productos
        $sql = "SELECT * FROM food";
        $result = $conn->query($sql);
        
        if ($result->num_rows > 0) {
            // Imprimir los datos de cada fila
            while($row = $result->fetch_assoc()) {
                echo '<div class="product" data-id="' . $row['ID'] . '">';
                echo '<div class="product-photo">';
                echo '<img src="data:image/png;base64,'.base64_encode($row['Picture']).'" alt="Imagen del producto">';
                echo '</div>';
                echo '<div class="product-details">';
                echo '<div class="product-info">';
                echo '<p><strong>Nombre:</strong> ' . $row['FoodName'] . '</p>';
                echo '<p><strong>Categoría:</strong> ' . $row['Categoria'] . '</p>';
                echo '<p data-price="' . $row['Precio'] . '"><strong>Precio:</strong> $' . number_format($row['Precio'], 2) . '</p>';
                echo '</div>';
                if ($isAdmin) {
                    echo '<form method="POST" action="index.php">';
                    echo '<input type="hidden" name="product_id" value="' . $row['ID'] . '">';
                    echo '<button type="submit" name="delete_product" class="eliminateproduct">Eliminar Producto</button>';
                    echo '</form>';
                } else {
                    echo '<div class="product-buton">';
                    echo '<button class="shop">Agregar al carrito</button>';
                    echo '<button class="less"><</button>';
                    echo '<div class="cantidad">';
                    echo '<p class="ctext">1</p>';
                    echo '</div>';
                    echo '<button class="more">></button>';
                    echo '</div>';
                }
                echo '</div>';
                echo '</div>';
            }
        } else {
            echo "0 resultados";
        }
        $conn->close();
        ?>
    </div>