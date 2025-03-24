// Función para actualizar el reloj
function actualizarReloj() {
    console.log("Actualizando reloj..."); // Mensaje de depuración
    const ahora = new Date();
    const fecha = ahora.toLocaleDateString('es-ES'); // Formato de fecha en español
    const hora = ahora.toLocaleTimeString('es-ES'); // Formato de hora en español
    document.getElementById('reloj').innerText = `${fecha} - ${hora}`;
}

// Actualizar el reloj cada segundo
setInterval(actualizarReloj, 1000);

// Llamar a la función al cargar la página
actualizarReloj();