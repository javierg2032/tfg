/* Popup Usuario */
const userIcon = document.getElementById("userIcon");
const loginPopup = document.getElementById("loginPopup");

// Alternar visibilidad al hacer clic en el icono
userIcon.addEventListener("click", (e) => {
  e.stopPropagation(); // Evita que el clic se propague y cierre el popup
  loginPopup.style.display =
    loginPopup.style.display === "block" ? "none" : "block";
});

// Evitar que clics dentro del popup cierren el popup
loginPopup.addEventListener("click", (e) => {
  e.stopPropagation();
});

// Cerrar si se hace clic fuera del icono o popup
window.addEventListener("click", () => {
  loginPopup.style.display = "none";
});

/* Carrusel */

let diapositivaActual = 0;
const diapositivas = document.querySelectorAll(".diapositiva");
const diapositivasTotales = diapositivas.length;
let intervalo; // ← declaración necesaria

function muestraDiapositiva(index) {
  diapositivas.forEach((diapositiva, i) => {
    diapositiva.classList.remove("diapositiva-activa");
    if (i === index) diapositiva.classList.add("diapositiva-activa");
  });
}

// Botones manuales
document.querySelector(".siguiente").addEventListener("click", () => {
  diapositivaActual = (diapositivaActual + 1) % diapositivasTotales;
  muestraDiapositiva(diapositivaActual);
  reiniciaIntervalo();
});

document.querySelector(".previo").addEventListener("click", () => {
  diapositivaActual =
    (diapositivaActual - 1 + diapositivasTotales) % diapositivasTotales;
  muestraDiapositiva(diapositivaActual);
  reiniciaIntervalo();
});

// Carrusel automático
function reiniciaIntervalo() {
  clearInterval(intervalo);
  intervalo = setInterval(() => {
    diapositivaActual = (diapositivaActual + 1) % diapositivasTotales;
    muestraDiapositiva(diapositivaActual);
  }, 6000);
}

// Inicial
muestraDiapositiva(diapositivaActual);
reiniciaIntervalo();
