let cropper;
let isCropped = false; // Variable de estado para verificar si se ha realizado el recorte

document.getElementById('selectButton').addEventListener('click', function() {
    document.getElementById('imageInput').click();
});

document.getElementById('imageInput').addEventListener('change', function(event) {
    const files = event.target.files;
    if (files.length > 0) {
        const file = files[0];
        const reader = new FileReader();

        reader.onload = function(e) {
            const image = document.getElementById('image');
            image.src = e.target.result;

            // Muestra el menú flotante
            document.getElementById('menuFlotante').classList.add('mostrar');

            // Espera a que la imagen esté completamente cargada
            image.onload = function() {
                // Inicializa Cropper.js
                if (cropper) {
                    cropper.destroy();
                }
                cropper = new Cropper(image, {
                    aspectRatio: 1,
                    viewMode: 1,
                    autoCropArea: 1,
                    zoomable: false, // Deshabilita el zoom
                    wheelZoomRatio: 0, // Deshabilita el zoom con la rueda del mouse
                    ready() {
                        // Escalar el área de recorte a 300px de ancho
                        const containerData = cropper.getContainerData();
                        const cropBoxData = cropper.getCropBoxData();
                        const aspectRatio = cropBoxData.width / cropBoxData.height;

                        cropper.setCropBoxData({
                            left: (containerData.width - 300) / 2,
                            top: (containerData.height - 300 / aspectRatio) / 2,
                            width: 300,
                            height: 300 / aspectRatio
                        });
                    }
                });
            };
        };

        reader.readAsDataURL(file);

        // Restablecer el valor del input para que siempre dispare el evento change
        event.target.value = '';
    }
});

document.getElementById('cropButton').addEventListener('click', function() {
    if (cropper) {
        const canvas = cropper.getCroppedCanvas({
            width: 150, // Establece el ancho del canvas recortado a 150px
            height: 150 // Establece la altura del canvas recortado a 150px
        });
        const croppedImageDataURL = canvas.toDataURL();
        const previewImage = document.getElementById('previewImage');
        previewImage.src = croppedImageDataURL;
        previewImage.style.display = 'block'; // Muestra la imagen recortada
        previewImage.style.position = 'fixed';
        previewImage.style.width = '150px'; // Asegura que el ancho sea de 150px
        previewImage.style.height = '150px'; // Asegura que la altura sea de 150px
        document.getElementById('resultContainer').classList.remove('empty');

        // Ocultar el menú flotante
        document.getElementById('menuFlotante').classList.remove('mostrar');
        isCropped = true; // Actualiza la variable de estado
    }
});

document.getElementById('saveButton').addEventListener('click', function(event) {
    event.preventDefault(); // Evitar el envío del formulario por defecto

    if (!isCropped) { // Verifica si la imagen ha sido recortada
        alert('Por favor, recorta la imagen antes de guardarla.');
        return;
    }

    const croppedImage = document.querySelector('#previewImage');
    const foodForm = document.getElementById('foodForm');
    const formData = new FormData(foodForm);
    if (croppedImage) {
        const imageData = croppedImage.src;

        formData.append('image', imageData);

        // Enviar la imagen y los datos del formulario al servidor
        fetch('save_image.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('Imagen guardada correctamente.');
            } else {
                alert('Error al guardar la imagen: ' + data.error);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Error al guardar la imagen.');
        });
    } else {
        alert('No hay imagen recortada para guardar.');
    }
});
