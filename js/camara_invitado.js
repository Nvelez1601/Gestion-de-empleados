window.onload = async function () {
    const cameraContainer = document.getElementById("contenedor-camara");
    const cameraStream = document.getElementById("mostrar-camara");
    const canvas = document.createElement("canvas");
    const context = canvas.getContext("2d");

    cameraStream.setAttribute("autoplay", "");
    cameraStream.setAttribute("playsinline", "");
    cameraContainer.style.display = "block";

    const mostrarNotificacion = (mensaje, tipo) => {
        const notificacion = document.createElement('div');
        notificacion.className = `notificacion ${tipo}`;
        notificacion.innerHTML = mensaje.replace(/\n/g, '<br>');
        document.body.appendChild(notificacion);
        
        setTimeout(() => {
            notificacion.classList.add('mostrar');
        }, 100);
        
        setTimeout(() => {
            notificacion.classList.remove('mostrar');
            setTimeout(() => {
                document.body.removeChild(notificacion);
            }, 500);
        }, 5000);
    };

    const registrarInvitado = async (contenidoQR) => {
        try {
            const response = await fetch('../php/guardar_invitado.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `contenido_qr=${encodeURIComponent(contenidoQR)}`
            });
            
            const resultado = await response.json();
            
            if(resultado.exito) {
                mostrarNotificacion(
                    `✅ Invitado registrado<br>
                    <strong>Nombre:</strong> ${resultado.datos.nombre}<br>
                    <strong>Correo:</strong> ${resultado.datos.correo}<br>
                    <strong>Hora:</strong> ${resultado.datos.hora}`, 
                    'exito'
                );
            } else {
                mostrarNotificacion(`❌ Error: ${resultado.mensaje}`, 'error');
            }
            
            // Reiniciar cámara después de 3 segundos
            setTimeout(() => {
                detenerCamara();
                location.reload();
            }, 3000);
            
        } catch (error) {
            console.error('Error:', error);
            mostrarNotificacion('⚠️ Error de conexión con el servidor', 'error');
            setTimeout(() => {
                detenerCamara();
                location.reload();
            }, 3000);
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
                const contenidoQR = qrCode.data.trim();
                detenerCamara();
                registrarInvitado(contenidoQR);
                return;
            }

            requestAnimationFrame(scanQR);

        } catch (error) {
            console.error("Error en escaneo:", error);
            mostrarNotificacion("⚠️ Error al escanear el código QR", 'error');
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
        mostrarNotificacion("⚠️ Error al acceder a la cámara: " + error.message, 'error');
    }
};

// Función para detener la cámara
window.detenerCamara = function () {
    const video = document.getElementById('mostrar-camara');
    if (video.srcObject) {
        video.srcObject.getTracks().forEach(track => track.stop());
    }
};

// Estilos para las notificaciones
const estiloNotificaciones = document.createElement('style');
estiloNotificaciones.textContent = `
.notificacion {
    position: fixed;
    top: 20px;
    left: 50%;
    transform: translateX(-50%) translateY(-100px);
    padding: 15px 25px;
    border-radius: 8px;
    color: white;
    font-family: 'Poppins', sans-serif;
    font-size: 16px;
    z-index: 1000;
    transition: transform 0.3s ease;
    max-width: 80%;
    text-align: left;
    box-shadow: 0 4px 12px rgba(0,0,0,0.15);
    opacity: 0;
    line-height: 1.6;
}

.notificacion.exito {
    background-color: #4CAF50;
    border-left: 5px solid #2E7D32;
}

.notificacion.error {
    background-color: #F44336;
    border-left: 5px solid #C62828;
}

.notificacion.mostrar {
    transform: translateX(-50%) translateY(0);
    opacity: 1;
}

.notificacion strong {
    font-weight: 600;
}
`;
document.head.appendChild(estiloNotificaciones);