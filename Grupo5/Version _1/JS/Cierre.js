function validarCampos() {
  const primera = document.getElementById("primera").value;
  const segunda = document.getElementById("segunda").value;
  const tercera = document.getElementById("tercera").value;

  if (primera === "" || segunda === "" || tercera === "") {
    alert("Debe ingresar los tres números ganadores.");
    return false;
  }

  return true;
}

document
  .getElementById("validarResultado")
  .addEventListener("click", function () {
    if (!validarCampos()) {
      return;
    }

    const confirmarPrimera = document.getElementById("confirmarPrimera").value;
    const confirmarSegunda = document.getElementById("confirmarSegunda").value;
    const confirmarTercera = document.getElementById("confirmarTercera").value;

    const primera = document.getElementById("primera").value;
    const segunda = document.getElementById("segunda").value;
    const tercera = document.getElementById("tercera").value;

    if (
      confirmarPrimera === "" ||
      confirmarSegunda === "" ||
      confirmarTercera === ""
    ) {
      alert("Debe confirmar los tres números ganadores.");
      return;
    }

    if (
      primera !== confirmarPrimera ||
      segunda !== confirmarSegunda ||
      tercera !== confirmarTercera
    ) {
      alert("Los números ingresados y confirmados no coinciden.");
      return;
    }

    const automaticaPrimera =
      document.getElementById("automaticaPrimera").value;

    const automaticaSegunda =
      document.getElementById("automaticaSegunda").value;

    const automaticaTercera =
      document.getElementById("automaticaTercera").value;

    if (
      automaticaPrimera === "--" ||
      automaticaSegunda === "--" ||
      automaticaTercera === "--"
    ) {
      alert("Debe cargar primero el resultado automático.");
      return;
    }

    const primeraManual = Number(primera);
    const segundaManual = Number(segunda);
    const terceraManual = Number(tercera);

    const primeraAutomatica = Number(automaticaPrimera);
    const segundaAutomatica = Number(automaticaSegunda);
    const terceraAutomatica = Number(automaticaTercera);

    if (
      primera === automaticaPrimera &&
      segunda === automaticaSegunda &&
      tercera === automaticaTercera
    ) {
      //document.getElementById("estadoResultado").textContent = "Validado";

      cambiarEstado("Validado");

      bloquearResultado();

      document.getElementById("usuarioResultado").textContent =
        "usuario de prueba";

      document.getElementById("fechaResultado").textContent =
        new Date().toLocaleString();

      buscarGanadores();
      //alert("El resultado fue validado correctamente.");
    } else {
      //document.getElementById("estadoResultado").textContent = "En revisión";

      cambiarEstado("En revisión");

      alert("Los resultados no coinciden. El resultado queda en revisión.");
    }
  });

$("#cargarResultado").click(function () {
  $.ajax({
    url: "/Version _1/Prueba_Resultado/Resultado.xml",
    dataType: "xml",
    success: function (xml) {
      const primera = $(xml).find("primera").text();
      const segunda = $(xml).find("segunda").text();
      const tercera = $(xml).find("tercera").text();

      $("#automaticaPrimera").val(primera);
      $("#automaticaSegunda").val(segunda);
      $("#automaticaTercera").val(tercera);

      alert("Resultado automático cargado correctamente.");
    },

    error: function () {
      alert("No se pudo cargar el resultado automático.");
    },
  });
});

function buscarGanadores() {
    
  const estado = document.getElementById("estadoResultado").textContent.trim();

  if (estado !== "Validado") {
    alert("El resultado debe estar validado antes de buscar los ganadores.");
    return;
  }
  const primera = document.getElementById("primera").value;
  const segunda = document.getElementById("segunda").value;
  const tercera = document.getElementById("tercera").value;

  $.ajax({
    url: "/Version _1/Prueba_Resultado/Jugada.xml",
    dataType: "xml",

    success: function (xml) {
      $("#tablaGanadores").empty();

      $(xml)
        .find("jugada")
        .each(function () {
          const codigoTicket = $(this).find("codigoTicket").text();

          const modalidad = $(this).find("modalidad").text();

          const numero = $(this).find("numero").text();

          const posicion = $(this).find("posicion").text();

          let ganador = false;

          if (posicion === "Primera" && numero === primera) {
            ganador = true;
          }

          if (posicion === "Segunda" && numero === segunda) {
            ganador = true;
          }

          if (posicion === "Tercera" && numero === tercera) {
            ganador = true;
          }

          if (ganador) {
            const fila = `
                        <tr>
                            <td>${codigoTicket}</td>
                            <td>${modalidad}</td>
                            <td>${posicion}</td>
                            <td>${numero}</td>
                        </tr>
                    `;

            $("#tablaGanadores").append(fila);
          }
        });
    },

    error: function () {
      alert("No se pudieron cargar las jugadas.");
    },
  });
}

function cambiarEstado(estado) {
  const elementoEstado = document.getElementById("estadoResultado");

  elementoEstado.textContent = estado;

  elementoEstado.classList.remove(
    "text-bg-secondary",
    "text-bg-success",
    "text-bg-warning",
  );

  if (estado === "Validado") {
    elementoEstado.classList.add("text-bg-success");
  } else if (estado === "En revisión") {
    elementoEstado.classList.add("text-bg-warning");
  } else {
    elementoEstado.classList.add("text-bg-secondary");
  }
}

function bloquearResultado() {
  document.getElementById("primera").disabled = true;
  document.getElementById("segunda").disabled = true;
  document.getElementById("tercera").disabled = true;

  document.getElementById("confirmarPrimera").disabled = true;
  document.getElementById("confirmarSegunda").disabled = true;
  document.getElementById("confirmarTercera").disabled = true;
}
