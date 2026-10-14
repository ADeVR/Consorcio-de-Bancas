class BotonPagar {
  static crear(numeroBoleto) {
    return `
      <button
        type="button"
        class="btn btn-pagar btn-sm"
        data-boleto="${numeroBoleto}"
      >
        Pagar
      </button>
    `;
  }
}

document.addEventListener("DOMContentLoaded", function () {
  document.querySelectorAll(".columna-accion").forEach(function (celda) {
    const fila = celda.closest("tr");
    const numeroBoleto = fila.querySelector(".numero-boleto").textContent.trim();

    celda.innerHTML = BotonPagar.crear(numeroBoleto);
  });
});