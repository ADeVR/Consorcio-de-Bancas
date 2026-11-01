/* Exportación sin librerías. Excel = XML SpreadsheetML (.xls) · CSV = texto. Exporta las filas que muestra el reporte. */
const Exportar = (() => {
  const COLUMNAS = ['Fecha y hora', 'Ticket', 'Operación', 'Importe', 'Usuario', 'Saldo resultante'];
  const celdas = (f) => [f.fecha_hora, f.ticket ?? '', f.operacion || f.tipo, f.importe ?? '', f.usuario, f.saldo_resultante ?? ''];
  const esc = (t) => String(t).replace(/[<>&"]/g, (c) => ({ '<': '&lt;', '>': '&gt;', '&': '&amp;', '"': '&quot;' }[c]));
  const nombre = (ext) => 'reporte_caja_' + new Date().toISOString().slice(0, 10) + '.' + ext;

  function descargar(texto, tipo, archivo) {
    const a = document.createElement('a');
    a.href = URL.createObjectURL(new Blob(['\ufeff' + texto], { type: tipo }));
    a.download = archivo;
    a.click();
    // Revocar después del click para evitar cancelaciones en algunos navegadores.
    setTimeout(() => URL.revokeObjectURL(a.href), 1000);
  }

  const fila = (v) => '<Row>' + v.map((c) => '<Cell><Data ss:Type="String">' + esc(c) + '</Data></Cell>').join('') + '</Row>';

  return {
    xml(filas) {
      const cuerpo = [COLUMNAS].concat(filas.filter((f) => f.tipo !== 'relevo' && f.tipo !== 'cierre').map(celdas)).map(fila).join('');
      descargar('<?xml version="1.0" encoding="UTF-8"?>\n<?mso-application progid="Excel.Sheet"?>\n'
        + '<Workbook xmlns="urn:schemas-microsoft-com:office:spreadsheet" xmlns:ss="urn:schemas-microsoft-com:office:spreadsheet">'
        + '<Worksheet ss:Name="Caja"><Table>' + cuerpo + '</Table></Worksheet></Workbook>', 'application/vnd.ms-excel', nombre('xls'));
    },
    csv(filas) {
      // Evita que Excel/Calc ejecuten fórmulas suministradas por usuarios en CSV.
      const seguro = (c) => { const t = String(c ?? ''); return /^[\s]*[=+@-]/.test(t) ? '\'' + t : t; };
      const linea = (v) => v.map((c) => '"' + seguro(c).replace(/"/g, '""') + '"').join(',');
      descargar([COLUMNAS].concat(filas.filter((f) => f.tipo !== 'relevo' && f.tipo !== 'cierre').map(celdas)).map(linea).join('\r\n'), 'text/csv', nombre('csv'));
    },
  };
})();
