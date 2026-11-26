<?php
// Página del ticket para imprimir (formato de impresora térmica de 80 mm)
// Uso: ticket_imprimir.php?codigo=TK00000013
require_once __DIR__ . '/includes/auth.php';

$codigo = isset($_GET['codigo']) ? strtoupper(trim($_GET['codigo'])) : '';

// 1) Buscar el ticket y quién lo vendió
$st = $pdo->prepare(
    "SELECT t.id_ticket, t.codigo_ticket, t.fecha_hora, t.total, t.estado,
            t.id_usuario, u.usuario
     FROM ticket t
     JOIN usuarios u ON u.id = t.id_usuario
     WHERE t.codigo_ticket = ?"
);
$st->execute([$codigo]);
$t = $st->fetch();

// Igual que en el resto del módulo: el agente solo ve sus tickets, el admin todos
if (!$t || ($_SESSION['rol'] !== 'admin' && (int) $t['id_usuario'] !== (int) $_SESSION['usuario_id'])) {
    http_response_code(404);
    exit('Ticket no encontrado.');
}

// 2) Buscar sus jugadas (misma consulta que usa api/tickets.php)
$sd = $pdo->prepare(
    "SELECT d.numeros, d.monto, j.modalidad,
            l.nombre AS loteria, s.nombre AS sorteo
     FROM jugada_detalle d
     JOIN juegos j   ON j.id = d.id_tipo
     JOIN loterias l ON l.id = d.id_loteria
     JOIN sorteos s  ON s.id = d.id_sorteo
     WHERE d.id_ticket = ?
     ORDER BY d.id_detalle"
);
$sd->execute([$t['id_ticket']]);
$jugadas = $sd->fetchAll();

// Atajo para escribir texto de forma segura en el HTML
function e($texto) {
    return htmlspecialchars((string) $texto, ENT_QUOTES, 'UTF-8');
}
?>
<!doctype html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Ticket <?= e($t['codigo_ticket']) ?></title>
<style>
    /* Tamaño del papel: 80 mm de ancho, el largo según el contenido */
    @page { size: 80mm auto; margin: 0; }

    body {
        width: 72mm;              /* un poco menos que el papel, por los bordes */
        margin: 0 auto;
        padding: 4mm 0;
        font-family: "Courier New", monospace;
        font-size: 12px;
        line-height: 1.5;
        color: #000;
    }
    .centro  { text-align: center; }
    .grande  { font-size: 16px; font-weight: bold; }
    .linea   { border-top: 1px dashed #000; margin: 10px 0; }
    .fila    { display: flex; justify-content: space-between; }
    .anulado { font-size: 18px; font-weight: bold; border: 2px solid #000; padding: 4px; }
    .jugada  { margin-bottom: 8px; } 

</style>
</head>
<body>

    <div class="centro grande">CONSORCIO DE BANCAS</div>
    <div class="centro">Módulo 4 - Jugadas</div>

    <div class="linea"></div>

    <div>Ticket: <b><?= e($t['codigo_ticket']) ?></b></div>
    <div>Fecha:  <?= e(date('d/m/Y h:i A', strtotime($t['fecha_hora']))) ?></div>
    <div>Vendedor: <?= e($t['usuario']) ?></div>

    <?php if ($t['estado'] === 'anulado'): ?>
        <div class="linea"></div>
        <div class="centro anulado">*** TICKET ANULADO ***</div>
    <?php endif; ?>

    <div class="linea"></div>

    <?php foreach ($jugadas as $j): ?>
        <div class="jugada">            
            <div><?= e($j['loteria'] . ' - ' . $j['sorteo']) ?></div>
            <div class="fila">
                <span><?= e($j['modalidad']) ?>: <b><?= e($j['numeros']) ?></b></span>
                <span>RD$ <?= e(number_format($j['monto'], 2)) ?></span>
            </div>
        </div>
    <?php endforeach; ?>

    <div class="linea"></div>

    <div class="fila grande">
        <span>TOTAL</span>
        <span>RD$ <?= e(number_format($t['total'], 2)) ?></span>
    </div>

    <div class="linea"></div>

    <div class="centro">Revise su ticket antes de retirarse.</div>
    <div class="centro">Sin ticket no se paga premio.</div>

    <script>
        // Al cargar la página, abrir la impresión automáticamente
        window.addEventListener("load", () => window.print());
    </script>
</body>
</html>
