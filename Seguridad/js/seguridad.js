document.addEventListener("DOMContentLoaded", () => {
  const clave = document.getElementById("clave");
  const boton = document.getElementById("mostrarClave");
  
  if (clave && boton) {
    boton.addEventListener("click", () => {
      const mostrar = clave.type === "password";
      clave.type = mostrar ? "text" : "password";
      boton.textContent = mostrar ? "Ocultar" : "Ver";
    });
  }
});
