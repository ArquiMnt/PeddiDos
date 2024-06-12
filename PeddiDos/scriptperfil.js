document.addEventListener('DOMContentLoaded', function() {
    const autoPerfilButtons = document.querySelectorAll('.AutoPerfilButton button');

    autoPerfilButtons.forEach(button => {
        button.addEventListener('click', function() {
            const imageUrl = this.getAttribute('data-image-url');
            setProfileImage(imageUrl);
        });
    });
    
    function setProfileImage(imageUrl) {
        console.log('Setting profile image from URL:', imageUrl); // Debugging log

        // Convert the image to a Blob
        fetch(imageUrl)
            .then(response => response.blob())
            .then(blob => {
                const formData = new FormData();
                formData.append('croppedImage', blob, 'profile.jpg');

                // Send the image to the server to update the database
                fetch('upload_profile_picture.php', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        // Update the profile image in the UI
                        const profileImageElement = document.getElementById('ImagePerfil');
                        profileImageElement.src = URL.createObjectURL(blob);
                    } else {
                        console.error('Error updating profile picture:', data.message);
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                });
            });
    }


    let cropperPerfil;

    document.getElementById('Perfilbutton').addEventListener('click', function() {
        document.getElementById('imagePerfil').click();
    });
    
    document.getElementById('imagePerfil').addEventListener('change', function(event) {
        const files = event.target.files;
        if (files.length > 0) {
            const file = files[0];
            const reader = new FileReader();
    
            reader.onload = function(e) {
                const image = document.getElementById('image-perfil');
                image.src = e.target.result;
    
                // Muestra el menú flotante
                document.getElementById('menuFlotante-perfil').classList.add('mostrar');
    
                // Espera a que la imagen esté completamente cargada
                image.onload = function() {
                    // Inicializa Cropper.js
                    if (cropperPerfil) {
                        cropperPerfil.destroy();
                    }
                    cropperPerfil = new Cropper(image, {
                        aspectRatio: 1,
                        viewMode: 1,
                        autoCropArea: 1,
                        zoomable: false, // Deshabilita el zoom
                        wheelZoomRatio: 0, // Deshabilita el zoom con la rueda del mouse
                        ready() {
                            // Escalar el área de recorte a 300px de ancho
                            const containerData = cropperPerfil.getContainerData();
                            const cropBoxData = cropperPerfil.getCropBoxData();
                            const aspectRatio = cropBoxData.width / cropBoxData.height;
    
                            cropperPerfil.setCropBoxData({
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
    
    document.getElementById('cropButton-perfil').addEventListener('click', function() {
        if (cropperPerfil) {
            const canvas = cropperPerfil.getCroppedCanvas({
                width: 150, // Establece el ancho del canvas recortado a 150px
                height: 150 // Establece la altura del canvas recortado a 150px
            });
            
            // Convertir el canvas a Blob
            canvas.toBlob(function(blob) {
                const formData = new FormData();
                formData.append('croppedImage', blob);
    
                // Enviar la imagen recortada al servidor
                fetch('upload_profile_picture.php', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.text())
                .then(data => {
                    // Manejar la respuesta del servidor
                    console.log(data);
                    // Actualizar la imagen de perfil en la interfaz de usuario
                    document.getElementById('ImagePerfil').src = canvas.toDataURL();
                    // Ocultar el menú flotante
                    document.getElementById('menuFlotante-perfil').classList.remove('mostrar');
                })
                .catch(error => {
                    console.error('Error:', error);
                });
            });
        }
    });
});
