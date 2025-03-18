// Ejecutar automáticamente al cargar la página de marcar entrada
// Utilizamos la API de MediaDevices.getUserMedia, que es una API nativa de navegadores actuales
window.onload = async function () {
    const cameraContainer = document.getElementById("contenedor-camara");
    const cameraStream = document.getElementById("mostrar-camara");

    // Aseguramos de que el contenedor de la cámara esté visible
    cameraContainer.style.display = "block";

    try {
        // Accede a la cámara del dispositivo
        const stream = await navigator.mediaDevices.getUserMedia({ video: true });
        cameraStream.srcObject = stream;
    } catch (error) {
        // Manejo de errores si no se puede acceder a la cámara
        console.error("No se pudo acceder a la cámara:", error);
        alert("Hubo un problema al acceder a la cámara. Verifica los permisos del navegador.");
    }
};

