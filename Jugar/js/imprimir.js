// Imprime un ticket sin salir de la página.
// Carga ticket_imprimir.php dentro de un marco (iframe) invisible;
// esa página se imprime sola cuando termina de cargar.
function imprimirTicket(codigo) {
  // Si quedó un marco de una impresión anterior, se quita
  const viejo = document.getElementById("marcoImpresion");
  if (viejo) viejo.remove();

  const marco = document.createElement("iframe");
  marco.id = "marcoImpresion";
  // Invisible pero "existe" (con display:none algunos navegadores no imprimen)
  marco.style.cssText = "position:fixed;right:0;bottom:0;width:0;height:0;border:0;";
  marco.src = "ticket_imprimir.php?codigo=" + encodeURIComponent(codigo);
  document.body.appendChild(marco);
}
