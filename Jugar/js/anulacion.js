const inpCodigo = document.getElementById("codigo");
const inpMotivo = document.getElementById("motivo");
const contador = document.getElementById("contador");
const mensaje = document.getElementById("mensaje");
const tbody = document.getElementById("listaSolicitudes");
const btnEnviar = document.getElementById("btnEnviar");

function crear(tag, texto, clase) {
  const e = document.createElement(tag);
  if (texto !== undefined) e.textContent = texto;
  if (clase) e.className = clase;
  return e;
}

function mostrarMensaje(tipo, texto) {
  mensaje.innerHTML = "";
  mensaje.appendChild(crear("div", texto, "alert alert-" + tipo));
}

const colores = {
  pendiente: "warning",
  aprobada: "success",
  rechazada: "danger",
};

function dibujar(lista) {
  tbody.innerHTML = "";

  if (!Array.isArray(lista) || lista.length === 0) {
    const tr = crear("tr");
    const td = crear("td", "No hay solicitudes registradas.", "text-center");
    td.colSpan = 5;
    tr.appendChild(td);
    tbody.appendChild(tr);
    return;
  }

  lista.forEach((s) => {
    const tr = crear("tr");
    [
      s.codigo_ticket,
      s.solicita,
      s.motivo,
      s.fecha_hora.substring(0, 16),
    ].forEach((t) => tr.appendChild(crear("td", t)));

    const tdEstado = crear("td");
    tdEstado.appendChild(
      crear("span", s.estado, "badge bg-" + (colores[s.estado] || "secondary")),
    );
    tr.appendChild(tdEstado);
    tbody.appendChild(tr);
  });
}

function cargar() {
  fetch("api/solicitudes.php")
    .then((r) => r.json())
    .then(dibujar)
    .catch(() =>
      mostrarMensaje("danger", "No se pudieron cargar las solicitudes."),
    );
}

inpMotivo.addEventListener("input", () => {
  contador.textContent = inpMotivo.value.length;
});

btnEnviar.addEventListener("click", () => {
  const codigo = inpCodigo.value.trim();
  const motivo = inpMotivo.value.trim();

  if (codigo === "")
    return mostrarMensaje("danger", "Escriba el código del ticket.");
  if (motivo.length < 5)
    return mostrarMensaje(
      "danger",
      "Escriba un motivo de al menos 5 caracteres.",
    );

  btnEnviar.disabled = true;
  fetch("api/solicitar_anulacion.php", {
    method: "POST",
    headers: { "Content-Type": "application/json" },
    body: JSON.stringify({ codigo: codigo, motivo: motivo }),
  })
    .then((r) => r.json())
    .then((res) => {
      if (res.ok) {
        mostrarMensaje(
          "success",
          "Solicitud enviada para el ticket " + res.codigo + ".",
        );
        inpCodigo.value = "";
        inpMotivo.value = "";
        contador.textContent = "0";
        cargar();
      } else {
        mostrarMensaje("danger", res.error);
      }
    })
    .catch(() =>
      mostrarMensaje("danger", "Error de comunicación con el servidor."),
    )
    .finally(() => {
      btnEnviar.disabled = false;
    });
});

cargar();
