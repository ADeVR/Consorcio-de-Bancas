const selSorteo = document.getElementById("sorteo");
const selJuego = document.getElementById("juego");
const contNumeros = document.getElementById("numeros");
const ayudaNum = document.getElementById("ayudaNumeros");
const inputMonto = document.getElementById("monto");
const ayudaMonto = document.getElementById("ayudaMonto");
const mensaje = document.getElementById("mensaje");
const tbody = document.getElementById("listaJugadas");
const totalEl = document.getElementById("total");

let sorteos = [];
let juegos = [];
let jugadas = []; // jugadas del ticket, todavía sin guardar

function mostrarMensaje(tipo, texto) {
  mensaje.innerHTML = "";
  const div = document.createElement("div");
  div.className = "alert alert-" + tipo;
  div.textContent = texto;
  mensaje.appendChild(div);
}

function juegoActual() {
  return juegos.find((j) => String(j.id) === selJuego.value);
}

function limpiarJuego() {
  juegos = [];
  selJuego.innerHTML = "";
  selJuego.add(new Option("Seleccione un sorteo primero", ""));
  selJuego.disabled = true;
  contNumeros.innerHTML = "";
  ayudaNum.textContent = "";
  inputMonto.value = "";
  inputMonto.disabled = true;
  ayudaMonto.textContent = "";
}

// 1) Cargar sorteos de hoy
fetch("api/sorteos.php")
  .then((r) => r.json())
  .then((data) => {
    sorteos = data;
    selSorteo.innerHTML = "";
    if (data.length === 0) {
      selSorteo.add(new Option("No hay sorteos disponibles hoy", ""));
      return;
    }
    selSorteo.add(new Option("Seleccione un sorteo", ""));
    data.forEach((s) => {
      selSorteo.add(
        new Option(
          s.loteria + " - " + s.nombre + " (" + s.hora.substring(0, 5) + ")",
          s.id,
        ),
      );
    });
  })
  .catch(() => mostrarMensaje("danger", "No se pudieron cargar los sorteos."));

// 2) Al elegir sorteo, cargar sus tipos de jugada
selSorteo.addEventListener("change", () => {
  limpiarJuego();
  if (!selSorteo.value) return;

  fetch("api/juegos.php?sorteo_id=" + encodeURIComponent(selSorteo.value))
    .then((r) => r.json())
    .then((data) => {
      juegos = data;
      selJuego.innerHTML = "";
      selJuego.add(new Option("Seleccione un tipo", ""));
      data.forEach((j) => selJuego.add(new Option(j.modalidad, j.id)));
      selJuego.disabled = false;
    })
    .catch(() =>
      mostrarMensaje("danger", "No se pudieron cargar los tipos de jugada."),
    );
});

// 3) Al elegir tipo, mostrar 1, 2 o 3 casillas según la modalidad
selJuego.addEventListener("change", () => {
  contNumeros.innerHTML = "";
  const j = juegoActual();
  if (!j) {
    ayudaNum.textContent = "";
    ayudaMonto.textContent = "";
    inputMonto.disabled = true;
    return;
  }

  for (let i = 0; i < Number(j.cantidad_numeros); i++) {
    const inp = document.createElement("input");
    inp.type = "text";
    inp.maxLength = 2;
    inp.inputMode = "numeric";
    inp.placeholder = "00";
    inp.className = "form-control text-center";
    inp.addEventListener("input", () => {
      inp.value = inp.value.replace(/\D/g, "");
    });
    contNumeros.appendChild(inp);
  }

  ayudaNum.textContent =
    "Ingrese " +
    j.cantidad_numeros +
    " número(s) entre " +
    j.numero_minimo +
    " y " +
    j.numero_maximo +
    ".";
  inputMonto.disabled = false;
  ayudaMonto.textContent =
    "Entre RD$ " +
    Number(j.monto_minimo).toFixed(2) +
    " y RD$ " +
    Number(j.monto_maximo).toFixed(2) +
    ".";
});

// 4) Agregar jugada a la lista del ticket
document.getElementById("btnAgregar").addEventListener("click", () => {
  const s = sorteos.find((x) => String(x.id) === selSorteo.value);
  const j = juegoActual();

  if (!s) return mostrarMensaje("danger", "Seleccione un sorteo.");
  if (!j) return mostrarMensaje("danger", "Seleccione un tipo de jugada.");

  const nums = [];
  for (const inp of contNumeros.querySelectorAll("input")) {
    if (inp.value === "")
      return mostrarMensaje("danger", "Complete todos los números.");
    const n = inp.value.padStart(2, "0");
    if (
      Number(n) < Number(j.numero_minimo) ||
      Number(n) > Number(j.numero_maximo)
    ) {
      return mostrarMensaje(
        "danger",
        "Los números deben estar entre " +
          j.numero_minimo +
          " y " +
          j.numero_maximo +
          ".",
      );
    }
    nums.push(n);
  }

  const monto = Number(inputMonto.value);
  if (
    !monto ||
    monto < Number(j.monto_minimo) ||
    monto > Number(j.monto_maximo)
  ) {
    return mostrarMensaje(
      "danger",
      "El monto debe estar entre RD$ " +
        Number(j.monto_minimo).toFixed(2) +
        " y RD$ " +
        Number(j.monto_maximo).toFixed(2) +
        ".",
    );
  }

  jugadas.push({
    id_sorteo: Number(s.id),
    id_loteria: Number(s.loteria_id),
    id_tipo: Number(j.id),
    loteria: s.loteria,
    sorteo: s.nombre,
    modalidad: j.modalidad,
    numeros: nums.join("-"),
    lista: nums,
    monto: monto,
  });

  mensaje.innerHTML = "";
  contNumeros.querySelectorAll("input").forEach((i) => {
    i.value = "";
  });
  inputMonto.value = "";
  dibujarLista();
});

// 5) Dibujar la tabla del ticket
function dibujarLista() {
  tbody.innerHTML = "";
  let total = 0;

  if (jugadas.length === 0) {
    const tr = document.createElement("tr");
    const td = document.createElement("td");
    td.colSpan = 5;
    td.className = "text-center";
    td.textContent = "Aún no hay jugadas en el ticket.";
    tr.appendChild(td);
    tbody.appendChild(tr);
  }

  jugadas.forEach((j, i) => {
    total += j.monto;
    const tr = document.createElement("tr");

    [
      j.loteria + " - " + j.sorteo,
      j.modalidad,
      j.numeros,
      "RD$ " + j.monto.toFixed(2),
    ].forEach((texto) => {
      const td = document.createElement("td");
      td.textContent = texto;
      tr.appendChild(td);
    });

    const tdBtn = document.createElement("td");
    const btn = document.createElement("button");
    btn.type = "button";
    btn.className = "btn btn-sm btn-outline-danger";
    btn.textContent = "Quitar";
    btn.addEventListener("click", () => {
      jugadas.splice(i, 1);
      dibujarLista();
    });
    tdBtn.appendChild(btn);
    tr.appendChild(tdBtn);
    tbody.appendChild(tr);
  });

  totalEl.textContent = "RD$ " + total.toFixed(2);
}

dibujarLista();

// 6) Guardar el ticket en la base de datos
document.getElementById("btnGuardar").addEventListener("click", () => {
  if (jugadas.length === 0) {
    return mostrarMensaje("danger", "Agregue al menos una jugada al ticket.");
  }

  const btn = document.getElementById("btnGuardar");
  btn.disabled = true;

  fetch("api/guardar_ticket.php", {
    method: "POST",
    headers: { "Content-Type": "application/json" },
    body: JSON.stringify({
      jugadas: jugadas.map((j) => ({
        id_tipo: j.id_tipo,
        numeros: j.lista,
        monto: j.monto,
      })),
    }),
  })
    .then((r) => r.json())
    .then((res) => {
      if (res.ok) {
        jugadas = [];
        dibujarLista();
        mostrarMensaje(
          "success",
          "Ticket " +
            res.codigo +
            " registrado. Total: RD$ " +
            Number(res.total).toFixed(2),
        );
      } else {
        mostrarMensaje("danger", res.error);
      }
    })
    .catch(() =>
      mostrarMensaje("danger", "Error de comunicación con el servidor."),
    )
    .finally(() => {
      btn.disabled = false;
    });
});
