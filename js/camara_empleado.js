
// Scrpits encargados del funcionamiento de la cámara lectora del QR
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

    // Funciones encargadas de validar el marcaje
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

    // Funciones encargadas de validar el QR de empleados
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

    // Manejod de errores para acceder a la cámara
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

window.detenerCamara = function () {
    const video = document.getElementById('mostrar-camara');
    if (video.srcObject) {
        video.srcObject.getTracks().forEach(track => track.stop());
    }
};

// Estilos de los errores de pantalla
const estiloNotificaciones = document.createElement('style');
estiloNotificaciones.textContent = `
.notificacion {
    position: fixed;
    top: 20px;
    left: 50%;
    transform: translateX(-50%) translateY(-100px);
    padding: 25px 35px;
    border-radius: 12px;
    color: white;
    font-family: 'Poppins', sans-serif;
    font-size: 20px;
    z-index: 1000;
    transition: transform 0.5s ease, opacity 0.5s ease;
    max-width: 90%;
    text-align: left;
    box-shadow: 0 10px 20px rgba(0, 0, 0, 0.2);
    opacity: 0;
    line-height: 1.6;
    background-color: #000000;
    border: 3px solid transparent;
    background-clip: padding-box;
    transition: all 0.4s ease;
}

.notificacion.exito {
    background-color: #4CAF50;
    border-left: 6px solid #2E7D32;
    box-shadow: 0 0 25px rgba(76, 175, 80, 0.9);
    animation: glow 1.5s infinite alternate;
}

.notificacion.error {
    background-color: #F44336;
    border-left: 6px solid #C62828;
    box-shadow: 0 0 25px rgba(244, 67, 54, 0.9);
    animation: glow 1.5s infinite alternate;
}

.notificacion.mostrar {
    transform: translateX(-50%) translateY(0);
    opacity: 1;
}

.notificacion strong {
    font-weight: 700;
    text-transform: uppercase;
    color: #f1f1f1;
}

@keyframes glow {
    0% { box-shadow: 0 0 10px rgba(255, 255, 255, 0.5); }
    100% { box-shadow: 0 0 30px rgba(255, 255, 255, 1); }
}
`;
document.head.appendChild(estiloNotificaciones);
