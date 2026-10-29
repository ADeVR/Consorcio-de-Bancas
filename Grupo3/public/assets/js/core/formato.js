/* Formato de presentación: «RD$ 1,000.00» y «8:55 PM». Sin aritmética: trabaja con el texto que envía el servidor. */
const Formato = (() => {
  const miles = (e) => e.replace(/\B(?=(\d{3})+(?!\d))/g, ',');

  function moneda(texto) {                         // "1020.00" | "-10.5"
    const m = /^(-?)(\d+)(?:\.(\d{1,2}))?$/.exec(String(texto).trim());
    if (!m) return String(texto);
    return (m[1] ? '−' : '') + 'RD$ ' + miles(m[2]) + '.' + (m[3] || '').padEnd(2, '0');
  }

  /** Entrada +RD$ 20.00 · salida −RD$ 500.00 · ninguno: sin signo (el signo no depende solo del color). */
  function movimiento(texto, efecto) {
    const sin = moneda(String(texto).replace(/^-/, ''));
    if (efecto === 'entrada') return '+' + sin;
    if (efecto === 'salida')  return '−' + sin;
    return sin;
  }

  function partes(sql) { return /^(\d{4})-(\d{2})-(\d{2})[ T](\d{2}):(\d{2})/.exec(String(sql)); }

  function hora(sql) {                             // 'YYYY-MM-DD HH:MM:SS' → '8:55 PM'
    const m = partes(sql);
    if (!m) return String(sql);
    const h = parseInt(m[4], 10);
    return (h % 12 || 12) + ':' + m[5] + ' ' + (h >= 12 ? 'PM' : 'AM');
  }

  function fecha(sql) {                            // → '20/10/2026'
    const m = partes(sql);
    return m ? m[3] + '/' + m[2] + '/' + m[1] : String(sql);
  }

  const fechaHora = (sql) => fecha(sql) + ' ' + hora(sql);

  return { moneda, movimiento, hora, fecha, fechaHora };
})();
