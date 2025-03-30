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
                let mensaje = resultado.mensaje;
                if(resultado.mensaje.includes('Horas trabajadas')) {
                    const horas = resultado.mensaje.split(': ')[1];
                    mensaje = `✅ <strong>${resultado.usuario.nombre_completo}</strong><br>Horas trabajadas hoy: ${horas}`;
                } else {
                    mensaje = `✅ <strong>${resultado.usuario.nombre_completo}</strong><br>${resultado.mensaje}`;
                }
                
                mostrarNotificacion(mensaje, 'exito');
            } else {
                mostrarNotificacion(`❌ ${resultado.mensaje}`, 'error');
            }
            
            // Reiniciar cámara después de mostrar notificación
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
                const qrID = qrCode.data.trim();

                if (!/^[A-Za-z0-9]{6}$/.test(qrID)) {
                    mostrarNotificacion("❌ El QR debe contener exactamente 6 caracteres alfanuméricos", 'error');
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
    font-family: Arial, sans-serif;
    font-size: 16px;
    z-index: 1000;
    transition: transform 0.3s ease;
    max-width: 80%;
    text-align: center;
    box-shadow: 0 4px 12px rgba(0,0,0,0.15);
    opacity: 0;
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
`;
document.head.appendChild(estiloNotificaciones);