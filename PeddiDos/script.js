document.addEventListener('DOMContentLoaded', function() {
    const menuBtn = document.getElementById('menu-btn');
    const menuPanel = document.getElementById('menu-panel');

    // Controlar la visibilidad del menú
    let menuVisible = false;

    function toggleMenu() {
        menuVisible = !menuVisible;
        if (menuVisible) {
            menuPanel.style.left = '0'; // Mostrar el menú
        } else {
            menuPanel.style.left = '-424px'; // Esconder el menú
        }
    }

    menuBtn.addEventListener('click', toggleMenu);

    // Ocultar el menú si se hace clic fuera de él
    document.addEventListener('click', function(event) {
        if (menuVisible && !menuPanel.contains(event.target) && event.target !== menuBtn) {
            toggleMenu();
        }
    });
});

document.addEventListener('DOMContentLoaded', function() {
    const agreeBtn = document.getElementById('agree-btn');
    const agreePanel = document.getElementById('agree-panel');

    // Controlar la visibilidad del menú
    let agreeVisible = false;

    function toggleMenu() {
        agreeVisible = !agreeVisible;
        if (agreeVisible) {
            agreePanel.style.right = '0'; // Mostrar el menú
        } else {
            agreePanel.style.right= '-324px'; // Esconder el menú
        }
    }
    
    agreeBtn.addEventListener('click', toggleMenu);

    // Ocultar el menú si se hace clic fuera de él
    document.addEventListener('click', function(event) {
        if (agreeVisible && !agreePanel.contains(event.target) && event.target !== agreeBtn) {
            toggleMenu();
        }
    });
});

document.addEventListener("DOMContentLoaded", function() {
    if (typeof isAdmin !== 'undefined' && isAdmin) {
        var carshopBtn = document.getElementById('carshop-btn');
        var agreeBtn = document.getElementById('agree-btn');
        
        if (carshopBtn) carshopBtn.style.display = 'none';
        if (agreeBtn) agreeBtn.style.display = 'block';
        
        var productButtons = document.querySelectorAll('.product-buton');
        productButtons.forEach(function(buttonGroup) {
            buttonGroup.style.display = 'none';
        });
    } else {
        var agreeBtn = document.getElementById('agree-btn');
        if (agreeBtn) agreeBtn.style.display = 'none';
    }
});

document.addEventListener('DOMContentLoaded', function() {
    const shopBtn = document.getElementById('carshop-btn');
    const shopPanel = document.getElementById('shop-panel');
    let shopVisible = false;

    function toggleMenu() {
        shopVisible = !shopVisible;
        if (shopVisible) {
            shopPanel.style.top = '90px'; // Mostrar el menú
        } else {
            shopPanel.style.top = '-524px'; // Esconder el menú
        }
    }

    shopBtn.addEventListener('click', toggleMenu);

    document.addEventListener('click', function(event) {
        if (shopVisible && !shopPanel.contains(event.target) && event.target !== shopBtn) {
            toggleMenu();
        }
    });
});


document.addEventListener('DOMContentLoaded', function() {
    const searcher = document.getElementById('searcher');
    let selectedCategory = 'All'; // Categoría seleccionada inicialmente es 'All'
    const filterButtons = document.querySelectorAll('.filter-button');

    function normalizeString(str) {
        return str.normalize('NFD').replace(/[\u0300-\u036f]/g, "").toLowerCase();
    }

    function filterProducts() {
        const filtro = normalizeString(searcher.value);
        const productos = document.querySelectorAll('.main-container .product');

        productos.forEach(function(producto) {
            const nombreProducto = normalizeString(producto.querySelector('.product-info p:first-child').innerText);
            const categoriaProducto = normalizeString(producto.querySelector('.product-info p:nth-child(2)').innerText.split(': ')[1]);

            const matchesSearch = nombreProducto.includes(filtro);
            const matchesCategory = selectedCategory === 'All' || categoriaProducto === normalizeString(selectedCategory);

            if (matchesSearch && matchesCategory) {
                producto.style.display = 'flex'; // Mostrar el producto si coincide con el filtro y la categoría
            } else {
                producto.style.display = 'none'; // Ocultar el producto si no coincide con el filtro o la categoría
            }
        });
    }

    searcher.addEventListener('input', filterProducts);

    filterButtons.forEach(button => {
        button.addEventListener('click', function() {
            selectedCategory = this.getAttribute('data-category'); // Obtener la categoría del botón presionado

            // Eliminar la clase 'active' de todos los botones
            filterButtons.forEach(btn => btn.classList.remove('active'));

            // Agregar la clase 'active' al botón presionado
            this.classList.add('active');

            filterProducts(); // Filtrar productos de acuerdo a la nueva categoría seleccionada

            // Enviar la categoría seleccionada al servidor
            updateUserCategory(selectedCategory);
        });
    });

    // Función para enviar la categoría seleccionada al servidor
    function updateUserCategory(category) {
        fetch('update_user_category.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({ category: category })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                console.log(data.message);
            } else {
                console.error(data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
        });
    }

    // Obtener la categoría del usuario al cargar la página
    function getUserCategory() {
        fetch('get_user_category.php')
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                selectedCategory = data.category;
                // Actualizar la interfaz para mostrar la categoría seleccionada
                filterButtons.forEach(btn => {
                    if (btn.getAttribute('data-category') === selectedCategory) {
                        btn.classList.add('active');
                    }
                });
                filterProducts();
            } else {
                console.error(data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
        });
    }

    getUserCategory();
});