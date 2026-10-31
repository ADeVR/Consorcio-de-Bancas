
class BotonPagar {

    static crear(numero) {
        return `
            <button
                type="button"
                class="btn btn-success btn-pagar btn-sm"
                data-numero="${esc(numero)}">
                Pagar
            </button>
        `;
    }

}

// Evita que un dato con símbolos raros rompa la página
function esc(texto) {
    return String(texto ?? "").replace(/[&<>"']/g, function (c) {
        return { "&": "&amp;", "<": "&lt;", ">": "&gt;", '"': "&quot;", "'": "&#39;" }[c];
    });
}

// VARIABLES GLOBALES

let boletos = [];

// INICIO

$(document).ready(function () {

    cargarGanadores();

    // Buscar boleto

    $("#numero-boleto").on("keyup", function () {

        let texto = $(this).val().toLowerCase();

        let resultado = boletos.filter(function (boleto) {

            return boleto.numero
                .toLowerCase()
                .includes(texto);

        });

        mostrarBoletos(resultado);

    });

    // Estado reclamación

    $("#estado-reclamacion").on("change", function () {

        let estado = $(this).val();

        if (estado === "pendiente") {
            $("#resultado-reclamacion").text("El premio se encuentra pendiente.");
        }
        else if (estado === "reclamado") {
            $("#resultado-reclamacion").text("El premio ha sido reclamado.");
        }
        else if (estado === "pagado") {
            $("#resultado-reclamacion").text("El premio ha sido pagado.");
        }
        else {
            $("#resultado-reclamacion").text("");
        }

    });

});

// CARGAR BOLETOS GANADORES DESDE LA BASE

function cargarGanadores() {

    $.ajax({

        url: "listar_ganadores.php",
        type: "GET",
        dataType: "json",

        success: function (datos) {

            boletos = datos.map(function (b) {
                return {
                    numero: b.numero,
                    modalidad: b.modalidad,
                    jugada: b.jugada,
                    sorteo: b.sorteo,
                    premio: parseFloat(b.premio),
                    estado: b.estado            // "pendiente" o "pagado"
                };
            });

            mostrarBoletos(boletos);

        },

        error: function () {
            alert("No se pudo cargar la lista de boletos ganadores.");
        }

    });

}

// MOSTRAR BOLETOS

function mostrarBoletos(lista) {

    $("#lista-boletos").empty();

    $.each(lista, function (i, boleto) {

        let pagado = boleto.estado === "pagado";

        let boton = pagado
            ? `<button type="button" class="btn btn-pagado btn-sm" disabled>Pagado</button>`
            : BotonPagar.crear(boleto.numero);

        $("#lista-boletos").append(`

            <tr>
                <td>${esc(boleto.numero)}</td>
                <td>${esc(boleto.modalidad)}</td>
                <td>${esc(boleto.jugada)}</td>
                <td>${esc(boleto.sorteo)}</td>
                <td>${esc(boleto.estado)}</td>
                <td>${boleto.premio.toLocaleString("es-DO")}</td>
                <td>
                    <select class="form-select form-select-sm metodo-pago"
                            aria-label="Método de pago" ${pagado ? "disabled" : ""}>
                        <option value="efectivo" selected>Efectivo</option>
                        <option value="transferencia">Transferencia</option>
                    </select>
                    <input type="text"
                           class="form-control form-control-sm cuenta-cliente"
                           placeholder="Cuenta del cliente"
                           style="display:none;">
                </td>
                <td class="columna-accion">${boton}</td>
            </tr>

        `);

    });

}

// PAGAR PREMIO

$(document).on("click", ".btn-pagar", function () {

    let boton = $(this);
    let numero = boton.data("numero");
    let fila = boton.closest("tr");
    let metodo = fila.find(".metodo-pago").val();
    let cuenta = fila.find(".cuenta-cliente").val().trim();

    let boleto = boletos.find(function (b) {
        return String(b.numero) === String(numero);
    });

    if (!boleto) {
        return;
    }

    // Evitar doble pago
    if (boleto.estado === "pagado") {
        alert("Este boleto ya fue pagado.");
        return;
    }

    // Validar cuenta si es transferencia
    if (metodo === "transferencia" && cuenta === "") {
        alert("Debes escribir la cuenta del cliente para pagar por transferencia.");
        return;
    }

    // Evita pulsar dos veces mientras se guarda
    boton.prop("disabled", true);

    $.ajax({

        url: "registrar_pago.php",
        type: "POST",
        dataType: "json",
        data: {
            numero: numero,
            metodo: metodo,
            cuenta: cuenta,
            token_csrf: $('meta[name="csrf"]').attr("content")
        },

        success: function (resp) {

            // El premio que cuenta es el que calculó el servidor
            boleto.estado = "pagado";
            boleto.premio = resp.premio;

            $("#estado-reclamacion").val("pagado");
            $("#resultado-reclamacion").text("El premio ha sido pagado.");

            $("#comprobante").html(`

                <h3>Comprobante de Pago</h3>

                <p><strong>Boleto:</strong> ${esc(boleto.numero)}</p>
                <p><strong>Modalidad:</strong> ${esc(boleto.modalidad)}</p>
                <p><strong>Jugada:</strong> ${esc(boleto.jugada)}</p>
                <p><strong>Sorteo:</strong> ${esc(boleto.sorteo)}</p>
                <p><strong>Monto pagado:</strong> RD$ ${resp.premio.toLocaleString("es-DO")}</p>
                <p><strong>Método de pago:</strong> ${resp.metodo === "transferencia" ? "Transferencia" : "Efectivo"}</p>

                ${resp.metodo === "transferencia" ? `
                <p><strong>Cuenta del cliente:</strong> ${esc(resp.cuenta)}</p>` : ""}

                <p><strong>Estado:</strong> Pagado</p>

            `);

            mostrarBoletos(boletos);

        },

        error: function (xhr) {

            let mensaje = "No se pudo registrar el pago.";

            if (xhr.responseJSON && xhr.responseJSON.mensaje) {
                mensaje = xhr.responseJSON.mensaje;
            }

            alert(mensaje);
            boton.prop("disabled", false);

        }

    });

});

// Mostrar el campo de cuenta solo si elige transferencia

$(document).on("change", ".metodo-pago", function () {

    let campo = $(this).closest("td").find(".cuenta-cliente");

    if ($(this).val() === "transferencia") {
        campo.show().focus();
    } else {
        campo.hide().val("");
    }

});