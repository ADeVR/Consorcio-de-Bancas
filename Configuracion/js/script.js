
var loterias = [];
var sorteos = [];
var juegos = [];

async function leerRespuestaJson(respuesta) {
    const texto = await respuesta.text();
    try {
        return JSON.parse(texto);
    } catch (error) {
        throw new Error(
            "El servidor no devolvió una respuesta PHP válida. Live Server no ejecuta PHP; inicia Apache/XAMPP y abre Configuración desde localhost."
        );
    }
}

async function cargarConfiguracion() {
    try {
        const respuesta = await fetch("php/api.php");
        const datos = await leerRespuestaJson(respuesta);
        if (!respuesta.ok || !datos.ok) {
            throw new Error(datos.mensaje || "No se pudo cargar la configuración.");
        }

        loterias = datos.loterias || [];
        sorteos = datos.sorteos || [];
        juegos = datos.juegos || [];
        actualizarLoterias();
        actualizarSorteos();
    } catch (error) {
        console.error(error);
        alert(error.message || "No se pudo cargar la configuración guardada.");
    }
}

async function guardarRegistro(datos) {
    const respuesta = await fetch("php/api.php", {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify(datos)
    });
    const resultado = await leerRespuestaJson(respuesta);
    if (!respuesta.ok || !resultado.ok) {
        throw new Error(resultado.mensaje || "No se pudo guardar el registro.");
    }
    return resultado;
}

function actualizarLoterias() {

    var lista = document.getElementById("listaLoterias");
    var selector = document.getElementById("loteriaSorteo");

    lista.innerHTML = "";
    selector.innerHTML = "";

    for (var i = 0; i < loterias.length; i++) {

        var elemento = document.createElement("li");
        elemento.textContent = loterias[i].nombre;
        lista.appendChild(elemento);

        var opcion = document.createElement("option");
        opcion.value = loterias[i].id;
        opcion.textContent = loterias[i].nombre;
        selector.appendChild(opcion);
    }
}

function actualizarSorteos() {

    var selector = document.getElementById("sorteoJuego");
    var lista = document.getElementById("listaSorteos");

    selector.innerHTML = "";
    lista.innerHTML = "";

    for (var i = 0; i < sorteos.length; i++) {

        var sorteo = sorteos[i];

        var opcion = document.createElement("option");
        opcion.value = sorteo.id;
        opcion.textContent =
            sorteo.loteria + " - " + sorteo.nombre;

        selector.appendChild(opcion);

        var elemento = document.createElement("li");
        elemento.textContent =
            sorteo.loteria + " | " +
            sorteo.nombre + " | " +
            sorteo.hora + " | " +
            (sorteo.dias || []).join(", ");

        lista.appendChild(elemento);
    }

    actualizarJuegos();
}

function agregarLoteria() {

    var nombre = document.getElementById("nombreLoteria").value.trim();

    if (nombre == "") {
        alert("Escriba el nombre de la lotería");
        return;
    }

    for (var i = 0; i < loterias.length; i++) {

        if (loterias[i].nombre.toLowerCase() == nombre.toLowerCase()) {
            alert("Esta lotería ya está registrada");
            return;
        }
    }

    const boton = document.getElementById("btnAgregarLoteria");
    boton.disabled = true;
    guardarRegistro({ accion: "loteria", nombre: nombre })
        .then(function (resultado) {
            document.getElementById("nombreLoteria").value = "";
            return cargarConfiguracion().then(function () { alert(resultado.mensaje); });
        })
        .catch(function (error) { alert(error.message); })
        .finally(function () { boton.disabled = false; });
}

function agregarSorteo() {

    if (loterias.length == 0) {
        alert("Primero registre una lotería");
        return;
    }

    var loteria = document.getElementById("loteriaSorteo").value;
    var nombre = document.getElementById("nombreSorteo").value.trim();
    var hora = document.getElementById("horaSorteo").value;

    var diasSeleccionados = document.querySelectorAll(
        ".dias input:checked"
    );

    var dias = [];

    for (var i = 0; i < diasSeleccionados.length; i++) {
        dias.push(diasSeleccionados[i].value);
    }

    if (nombre == "" || hora == "") {
        alert("Complete el nombre y la hora del sorteo");
        return;
    }

    if (dias.length == 0) {
        alert("Seleccione al menos un día");
        return;
    }

    var boton = document.querySelector("#seccion-sorteos button");
    boton.disabled = true;
    guardarRegistro({
        accion: "sorteo",
        loteria_id: loteria,
        nombre: nombre,
        hora: hora,
        dias: dias
    }).then(function (resultado) {
        document.getElementById("nombreSorteo").value = "";
        document.getElementById("horaSorteo").value = "";
        for (var i = 0; i < diasSeleccionados.length; i++) {
            diasSeleccionados[i].checked = false;
        }
        return cargarConfiguracion().then(function () { alert(resultado.mensaje); });
    }).catch(function (error) {
        alert(error.message);
    }).finally(function () {
        boton.disabled = false;
    });
}

function agregarJuego() {

    if (sorteos.length == 0) {
        alert("Primero registre un sorteo");
        return;
    }

    var sorteoId = document.getElementById("sorteoJuego").value;

    if (sorteoId == "") {
        alert("Seleccione un sorteo");
        return;
    }

    var modalidad = document.getElementById("modalidad").value;
    var cantidad = Number(document.getElementById("cantidadNumeros").value);

    var minimo = document.getElementById("numeroMinimo").value.trim();
    var maximo = document.getElementById("numeroMaximo").value.trim();

    var montoMinimo = Number(document.getElementById("montoMinimo").value);
    var montoMaximo = Number(document.getElementById("montoMaximo").value);

    if (cantidad < 1 || minimo == "" || maximo == "") {
        alert("Revise la cantidad y el rango de números");
        return;
    }

    if (montoMinimo <= 0 || montoMaximo < montoMinimo) {
        alert("Revise los montos mínimo y máximo");
        return;
    }

    var camposPremios = [
        { id: "premioPrimera", nombre: "Primera" },
        { id: "premioSegunda", nombre: "Segunda" },
        { id: "premioTercera", nombre: "Tercera" },
        { id: "premioPale", nombre: "Palé" },
        { id: "premioTripleta", nombre: "Tripleta" }
    ];

    var premios = [];

    for (var i = 0; i < camposPremios.length; i++) {

        var valor = document.getElementById(
            camposPremios[i].id
        ).value;

        if (valor != "") {

            var cantidadPremio = Number(valor);

            if (!Number.isFinite(cantidadPremio) || cantidadPremio < 0) {
                alert("Revise los valores de los premios");
                return;
            }

            premios.push({
                nombre: camposPremios[i].nombre,
                valor: cantidadPremio
            });
        }
    }

    var boton = document.querySelector("#seccion-modalidades button");
    boton.disabled = true;
    guardarRegistro({
        accion: "juego",
        sorteo_id: sorteoId,
        modalidad: modalidad,
        cantidad: cantidad,
        minimo: minimo,
        maximo: maximo,
        monto_minimo: montoMinimo,
        monto_maximo: montoMaximo,
        premios: premios
    }).then(function (resultado) {
        return cargarConfiguracion().then(function () { alert(resultado.mensaje); });
    }).catch(function (error) {
        alert(error.message);
    }).finally(function () {
        boton.disabled = false;
    });
}

function actualizarJuegos() {

    var lista = document.getElementById("listaJuegos");
    lista.innerHTML = "";

    for (var i = 0; i < juegos.length; i++) {

        var juego = juegos[i];

        var texto =
            juego.loteria + " | " +
            juego.sorteo + " | " +
            juego.modalidad +
            " | Números: " + juego.cantidad_numeros +
            " | Rango: " + juego.numero_minimo + "-" + juego.numero_maximo +
            " | Apuesta: RD$" + juego.monto_minimo +
            " a RD$" + juego.monto_maximo;

        if (juego.premios.length > 0) {

            texto += " | Premios por RD$1: ";

            for (var j = 0; j < juego.premios.length; j++) {

                texto += juego.premios[j].nombre +
                    " RD$" + juego.premios[j].valor;

                if (j < juego.premios.length - 1) {
                    texto += ", ";
                }
            }

        } else {
            texto += " | Premios: Por configurar";
        }

        var elemento = document.createElement("li");
        elemento.textContent = texto;
        lista.appendChild(elemento);
    }
}

cargarConfiguracion();


$(document).ready(function () {
    $("#btnAgregarLoteria").on("click", function () {
        const nombre = $("#nombreLoteria").val().trim();

        if (nombre === "") {
            alert("Escriba el nombre de la lotería");
            $("#nombreLoteria").focus();
            return;
        }

        agregarLoteria();
        $("#nombreLoteria").removeClass("campo-error");
    });

    $("#nombreLoteria").on("input", function () {
        if ($(this).val().trim() === "") {
            $(this).addClass("campo-error");
        } else {
            $(this).removeClass("campo-error");
        }
    });
});

