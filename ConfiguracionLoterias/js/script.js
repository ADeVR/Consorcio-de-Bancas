function agregarSorteo() {
  var nombre_sorteo = document.getElementById("nombreSorteo").value;
  var hora_sorteo = document.getElementById("horaSorteo").value;

  if (nombre_sorteo == "" || hora_sorteo == "") {
    alert("Complete el nombre y la hora del sorteo");
  } else {
    var dias = "";

    if (document.getElementById("lunes").checked) {
      dias += "Lunes ";
    }

    if (document.getElementById("martes").checked) {
      dias += "Martes ";
    }

    if (document.getElementById("miercoles").checked) {
      dias += "Miércoles ";
    }

    if (document.getElementById("jueves").checked) {
      dias += "Jueves ";
    }

    if (document.getElementById("viernes").checked) {
      dias += "Viernes ";
    }

    if (document.getElementById("sabado").checked) {
      dias += "Sábado ";
    }

    if (document.getElementById("domingo").checked) {
      dias += "Domingo ";
    }

    if (dias == "") {
      alert("Seleccione al menos un día para el sorteo");
    } else {
      var lista = document.getElementById("listaSorteos");

      var nuevoSorteo = document.createElement("li");

      nuevoSorteo.textContent =
        nombre_sorteo + " - " + hora_sorteo + " - " + dias;

      lista.appendChild(nuevoSorteo);

      document.getElementById("nombreSorteo").value = "";
      document.getElementById("horaSorteo").value = "";
    }
  }
}
