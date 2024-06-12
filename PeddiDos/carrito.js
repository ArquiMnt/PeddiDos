document.addEventListener('DOMContentLoaded', function() {
    // Botón "Agregar al carrito"
    document.querySelector('.main-container').addEventListener('click', function(event) {
        if (event.target.classList.contains('shop')) {
            const productElement = event.target.closest('.product');
            const productId = productElement.getAttribute('data-id');
            const quantityElement = productElement.querySelector('.ctext');
            const quantity = parseInt(quantityElement.textContent);

            addToCart(productId, quantity);
        }
    });

    function addToCart(productId, quantity) {
        fetch('update_cart.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({ productId: productId, quantity: quantity })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                console.log('Producto agregado al carrito');
                alert('Producto agregado al carrito');
            } else {
                console.error('Error al agregar producto al carrito:', data.message);
                alert('Error al agregar producto al carrito: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Error al agregar producto al carrito');
        });
    }

    // Incrementar y decrementar la cantidad de productos
    document.querySelector('.main-container').addEventListener('click', function(event) {
        if (event.target.classList.contains('more')) {
            const quantityElement = event.target.previousElementSibling.querySelector('.ctext');
            let quantity = parseInt(quantityElement.textContent);
            quantity++;
            quantityElement.textContent = quantity;
        } else if (event.target.classList.contains('less')) {
            const quantityElement = event.target.nextElementSibling.querySelector('.ctext');
            let quantity = parseInt(quantityElement.textContent);
            if (quantity > 1) {
                quantity--;
                quantityElement.textContent = quantity;
            }
        }
    });
});
