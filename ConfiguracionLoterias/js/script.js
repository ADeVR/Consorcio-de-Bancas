
var loterias = ["LEIDSA", "LOTEKA"];
var sorteos = [];
var juegos = [];

function actualizarLoterias() {

    var lista = document.getElementById("listaLoterias");
    var selector = document.getElementById("loteriaSorteo");

    lista.innerHTML = "";
    selector.innerHTML = "";

    for (var i = 0; i < loterias.length; i++) {

        var elemento = document.createElement("li");
        elemento.textContent = loterias[i];
        lista.appendChild(elemento);

        var opcion = document.createElement("option");
        opcion.value = loterias[i];
        opcion.textContent = loterias[i];
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
        opcion.value = i;
        opcion.textContent =
            sorteo.loteria + " - " + sorteo.nombre;

        selector.appendChild(opcion);

        var elemento = document.createElement("li");
        elemento.textContent =
            sorteo.loteria + " | " +
            sorteo.nombre + " | " +
            sorteo.hora + " | " +
            sorteo.dias.join(", ");

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

        if (loterias[i].toLowerCase() == nombre.toLowerCase()) {
            alert("Esta lotería ya está registrada");
            return;
        }
    }

    loterias.push(nombre);

    document.getElementById("nombreLoteria").value = "";

    actualizarLoterias();
    alert("Lotería registrada correctamente");
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

    sorteos.push({
        loteria: loteria,
        nombre: nombre,
        hora: hora,
        dias: dias
    });

    document.getElementById("nombreSorteo").value = "";
    document.getElementById("horaSorteo").value = "";

    for (var i = 0; i < diasSeleccionados.length; i++) {
        diasSeleccionados[i].checked = false;
    }

    actualizarSorteos();

    alert("Sorteo registrado correctamente");
}

function agregarJuego() {

    if (sorteos.length == 0) {
        alert("Primero registre un sorteo");
        return;
    }

    var indice = document.getElementById("sorteoJuego").value;

    if (indice == "") {
        alert("Seleccione un sorteo");
        return;
    }

    var sorteo = sorteos[Number(indice)];

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

    juegos.push({
        loteria: sorteo.loteria,
        sorteo: sorteo.nombre,
        modalidad: modalidad,
        cantidad: cantidad,
        minimo: minimo,
        maximo: maximo,
        montoMinimo: montoMinimo,
        montoMaximo: montoMaximo,
        premios: premios
    });

    actualizarJuegos();

    alert("Modalidad configurada correctamente");
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
            " | Números: " + juego.cantidad +
            " | Rango: " + juego.minimo + "-" + juego.maximo +
            " | Apuesta: RD$" + juego.montoMinimo +
            " a RD$" + juego.montoMaximo;

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

actualizarLoterias();
actualizarSorteos();
