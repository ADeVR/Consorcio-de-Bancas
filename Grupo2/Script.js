
class BotonPagar {
    static crear() {
        return `
            <button type="button" class="btn btn-pagar btn-sm">
                Pagar
            </button>
        `;
    }
}

document.addEventListener("DOMContentLoaded", function () {
    document.querySelectorAll(".columna-accion").forEach(function (celda) {
        celda.innerHTML = BotonPagar.crear();
    });
}); 