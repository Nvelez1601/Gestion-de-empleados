// js/main.js
window.onload = async function () {
    const cameraContainer = document.getElementById("contenedor-camara");
    const cameraStream = document.getElementById("mostrar-camara");
    const canvas = document.createElement("canvas");
    const context = canvas.getContext("2d");

    // Configuración para móviles
    cameraStream.setAttribute("autoplay", "");
    cameraStream.setAttribute("playsinline", "");
    cameraContainer.style.display = "block";

    const validarQR = async (qrID) => {
        try {
            const response = await fetch('../php/validar_qr.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `id=${encodeURIComponent(qrID)}`
            });
            
            const resultado = await response.json();
            
            if(resultado.exito) {
                alert(`✅ Acceso permitido\nBienvenido: ${resultado.usuario.nombre}`);
            } else {
                alert(`❌ Error: ${resultado.mensaje}`);
                setTimeout(() => location.reload(), 2000); // Recargar para reintentar
            }
            
        } catch (error) {
            console.error('Error en la validación:', error);
            alert('⚠️ Error de conexión con el servidor');
        }
    };

    const scanQR = () => {
        try {
            if (cameraStream.videoWidth === 0 || !cameraStream.srcObject) {
                requestAnimationFrame(scanQR);
                return;
            }

            canvas.width = cameraStream.videoWidth;
            canvas.height = cameraStream.videoHeight;
            context.drawImage(cameraStream, 0, 0, canvas.width, canvas.height);

            const imageData = context.getImageData(0, 0, canvas.width, canvas.height);
            const qrCode = jsQR(imageData.data, imageData.width, imageData.height, {
                inversionAttempts: "dontInvert"
            });

            if (qrCode) {
                const qrID = qrCode.data.trim();
                
                // Validar formato del ID
                if(!/^[A-Za-z0-9]{6}$/.test(qrID)) {
                    alert("❌ El QR debe contener exactamente 6 caracteres alfanuméricos");
                    detenerCamara();
                    setTimeout(() => location.reload(), 2000);
                    return;
                }
                
                detenerCamara();
                validarQR(qrID);
                return;
            }
            
            requestAnimationFrame(scanQR);

        } catch (error) {
            console.error("Error en escaneo:", error);
        }
    };

    try {
        const stream = await navigator.mediaDevices.getUserMedia({
            video: { 
                facingMode: "environment",
                width: { ideal: 1280 },
                height: { ideal: 720 }
            }
        });
        
        cameraStream.srcObject = stream;
        cameraStream.onplaying = () => scanQR();

    } catch (error) {
        alert("⚠️ Error al acceder a la cámara: " + error.message);
    }
};

// Función para detener la cámara
window.detenerCamara = function() {
    const video = document.getElementById('mostrar-camara');
    if (video.srcObject) {
        video.srcObject.getTracks().forEach(track => track.stop());
    }
};

// Código para transiciones de formularios (original)
const contenedor_registro = document.querySelector(".contenedor-registro");
const btnInicioSesion = document.getElementById("btn-inicio-sesion");
const btnRegistro = document.getElementById("btn-registro");

btnInicioSesion.addEventListener("click", () => {
    contenedor_registro.classList.remove("toggle");
});

btnRegistro.addEventListener("click", () => {
    contenedor_registro.classList.add("toggle");
});