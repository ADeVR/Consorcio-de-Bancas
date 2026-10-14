
class BotonPagar {
 
    static crear(numero) {
        return `
            <button
                type="button"
                class="btn btn-success btn-pagar btn-sm"
                data-numero="${numero}">
                Pagar
            </button>
        `;
    }
 
}
 

//VARIABLES GLOBALES


let boletos = [];
let porcentajeRetencion = 10;
 

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
 
            $("#resultado-reclamacion")
                .text("El premio se encuentra pendiente.");
 
        }
        else if (estado === "reclamado") {
 
            $("#resultado-reclamacion")
                .text("El premio ha sido reclamado.");
 
        }
        else if (estado === "pagado") {
 
            $("#resultado-reclamacion")
                .text("El premio ha sido pagado.");
 
        }
        else {
 
            $("#resultado-reclamacion").text("");
 
        }
 
    });
 
});
 

// CARGAR XML
 
function cargarGanadores() {
 
    $.ajax({
 
        url: "Pago.XML",   
        type: "GET",
        dataType: "xml",
 
        success: function (xml) {
 
            boletos = [];
 
            $(xml).find("boleto").each(function () {
 
                boletos.push({
 
                    numero: $(this).find("numero").text(),
                    modalidad: $(this).find("modalidad").text(),
                    jugada: $(this).find("jugada").text(),
                    sorteo: $(this).find("sorteo").text(),
                    premio: parseFloat($(this).find("premio").text()),
                    estado: $(this).find("estado").text()
 
                });
 
            });
 
            mostrarBoletos(boletos);
 
        },
 
        error: function () {
 
            alert("No se pudo cargar Pago.XML");
 
        }
 
    });
 
}
 
//MOSTRAR TABLA

 
function mostrarBoletos(lista) {
 
    $("#lista-boletos").empty();
 
    $.each(lista, function (i, boleto) {
 
        let boton;
 
        if (boleto.estado === "Pagado") {
 
            boton = `
                <button
                    type="button"
                    class="btn btn-secondary btn-sm"
                    disabled>
                    Pagado
                </button>
            `;
 
        }
        else {
 
            boton = BotonPagar.crear(boleto.numero);
 
        }
 
        $("#lista-boletos").append(`
 
            <tr>
 
                <td>${boleto.numero}</td>
 
                <td>${boleto.modalidad}</td>
 
                <td>${boleto.jugada}</td>
 
                <td>${boleto.sorteo}</td>
 
                <td>${boleto.estado}</td>
 
                <td>RD$ ${boleto.premio.toLocaleString("es-DO")}</td>
 
                <td class="columna-accion">
                    ${boton}
                </td>
 
            </tr>
 
        `);
 
    });
 
}
 
// =============================================
// CALCULAR PREMIO
// =============================================
 
function calcularPago(monto) {
 
    let descuento =
        monto * (porcentajeRetencion / 100);
 
    return monto - descuento;
 
}
 
// =============================================
// PAGAR PREMIO
// =============================================
 
$(document).on("click", ".btn-pagar", function () {
 
    let numero = $(this).data("numero");
 
    let boleto = boletos.find(function (b) {
 
        return String(b.numero) === String(numero);
 
    });
 
    if (!boleto) {
 
        return;
 
    }
 
    // Evitar doble pago
 
    if (boleto.estado === "Pagado") {
 
        alert("Este boleto ya fue pagado.");
 
        return;
 
    }
 
    let montoFinal =
        calcularPago(boleto.premio);
 
    boleto.estado = "Pagado";
 
    $("#estado-reclamacion")
        .val("pagado");
 
    $("#resultado-reclamacion")
        .text("El premio ha sido pagado.");
 
    $("#comprobante").html(`
 
        <h3>Comprobante de Pago</h3>
 
        <p>
            <strong>Boleto:</strong>
            ${boleto.numero}
        </p>
 
        <p>
            <strong>Modalidad:</strong>
            ${boleto.modalidad}
        </p>
 
        <p>
            <strong>Jugada:</strong>
            ${boleto.jugada}
        </p>
 
        <p>
            <strong>Sorteo:</strong>
            ${boleto.sorteo}
        </p>
 
        <p>
            <strong>Premio:</strong>
            RD$ ${boleto.premio.toLocaleString("es-DO")}
        </p>
 
        <p>
            <strong>Retención:</strong>
            ${porcentajeRetencion}%
        </p>
 
        <p>
            <strong>Monto Pagado:</strong>
            RD$ ${montoFinal.toLocaleString("es-DO")}
        </p>
 
        <p>
            <strong>Estado:</strong>
            Pagado
        </p>
 
    `);
 
    mostrarBoletos(boletos);
 
});
