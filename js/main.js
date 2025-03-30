// js/main.js

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



// Función para cerrar sesión y redirigir
function cerrarSesion() {
    // Redirección a index.html después de 500ms (medio segundo)
    setTimeout(function() {
        window.location.href = "index.html";
    }, 500);
}