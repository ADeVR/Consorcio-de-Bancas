function agregarLoteria() {
  var nombre_loteria = document.getElementById("nombreLoteria").value;

  if (nombre_loteria === "") {
    alert("Escriba el nombre de la loteria");
  } else {
    alert("Loteria agregada correctamente: " + nombre_loteria);
  }
}
