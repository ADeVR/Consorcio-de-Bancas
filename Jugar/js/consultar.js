const inpFecha = document.getElementById("fecha");
const inpCodigo = document.getElementById("codigo");
const tbody = document.getElementById("listaTickets");
const resumen = document.getElementById("resumen");
const mensaje = document.getElementById("mensaje");

function crear(tag, texto, clase) {
  const e = document.createElement(tag);
  if (texto !== undefined) e.textContent = texto;
  if (clase) e.className = clase;
  return e;
}

function dinero(n) {
  return "RD$ " + Number(n).toFixed(2);
}

function filaVacia(texto) {
  const tr = crear("tr");
  const td = crear("td", texto, "text-center");
  td.colSpan = 6;
  tr.appendChild(td);
  tbody.appendChild(tr);
}

function tablaDetalle(jugadas) {
  const tabla = crear("table", undefined, "table table-sm mb-0");
  const cab = crear("tr");
  ["Sorteo", "Tipo", "Números", "Monto", "Estado"].forEach((h) =>
    cab.appendChild(crear("th", h)),
  );
  const thead = crear("thead");
  thead.appendChild(cab);
  tabla.appendChild(thead);

  const cuerpo = crear("tbody");
  jugadas.forEach((j) => {
    const tr = crear("tr");
    [
      j.loteria + " - " + j.sorteo,
      j.modalidad,
      j.numeros,
      dinero(j.monto),
      j.estado,
    ].forEach((t) => tr.appendChild(crear("td", t)));
    cuerpo.appendChild(tr);
  });
  tabla.appendChild(cuerpo);
  return tabla;
}

function dibujar(tickets) {
  tbody.innerHTML = "";
  resumen.textContent = "";

  if (!Array.isArray(tickets) || tickets.length === 0) {
    filaVacia("No hay tickets para esa fecha.");
    return;
  }

  let total = 0;
  let activos = 0;

  tickets.forEach((t) => {
    if (t.estado === "activo") {
      total += Number(t.total);
      activos++;
    }

    const tr = crear("tr");
    tr.appendChild(crear("td", t.codigo_ticket));
    tr.appendChild(crear("td", t.fecha_hora.substring(11, 16)));
    tr.appendChild(crear("td", t.usuario));
    tr.appendChild(crear("td", dinero(t.total)));

    const tdEstado = crear("td");
    tdEstado.appendChild(
      crear(
        "span",
        t.estado,
        "badge bg-" + (t.estado === "activo" ? "success" : "secondary"),
      ),
    );
    tr.appendChild(tdEstado);

    const btn = crear(
      "button",
      "Ver detalle",
      "btn btn-sm btn-outline-secondary",
    );
    btn.type = "button";
    const tdBtn = crear("td");
    tdBtn.appendChild(btn);
    tr.appendChild(tdBtn);
    tbody.appendChild(tr);

    // Fila con el detalle, escondida hasta pulsar el botón
    const trDet = crear("tr", undefined, "d-none");
    const tdDet = crear("td");
    tdDet.colSpan = 6;
    tdDet.appendChild(tablaDetalle(t.jugadas));
    trDet.appendChild(tdDet);
    tbody.appendChild(trDet);

    btn.addEventListener("click", () => {
      const oculto = trDet.classList.toggle("d-none");
      btn.textContent = oculto ? "Ver detalle" : "Ocultar";
    });
  });

  resumen.textContent =
    tickets.length +
    " ticket(s). Vendido en tickets activos: " +
    dinero(total) +
    " (" +
    activos +
    ").";
}

function cargar() {
  mensaje.innerHTML = "";
  const q = new URLSearchParams({
    fecha: inpFecha.value,
    codigo: inpCodigo.value.trim(),
  });

  fetch("api/tickets.php?" + q.toString())
    .then((r) => r.json())
    .then(dibujar)
    .catch(() => {
      const div = crear(
        "div",
        "No se pudieron cargar los tickets.",
        "alert alert-danger",
      );
      mensaje.appendChild(div);
    });
}

document.getElementById("btnBuscar").addEventListener("click", cargar);
cargar();
