<?php
declare(strict_types=1);
namespace Caja\Core;

/**
 * Entrega el adaptador de cada puerto (conexión con otros grupos).
 *  - integracion.php = 'real'      → Adaptadores/Real/<Puerto>Real      (módulo real del otro grupo)
 *  - app.php fuente_datos = 'db'   → Adaptadores/Simulado/<Puerto>Simulado (tablas sim_* en MySQL)
 *  - cualquier otra fuente         → Adaptadores/Mock/<Puerto>Mock      (sin base de datos)
 */
final class Contenedor
{
    private const MAPA = [
        'identidad' => 'Identidad', 'jugada' => 'Jugada', 'operaciones' => 'Operaciones',
        'ventas_base' => 'VentasBase', 'auditoria' => 'Auditoria', 'hora' => 'Hora', 'catalogo' => 'Catalogo',
    ];
    private static array $inst = [];

    public static function puerto(string $nombre): object
    {
        if (!isset(self::MAPA[$nombre])) throw new \InvalidArgumentException("Puerto desconocido: {$nombre}");
        if (!isset(self::$inst[$nombre])) {
            $real  = (Config::get('integracion')[$nombre] ?? 'simulado') === 'real';
            $modo  = $real ? 'Real' : (Config::get('app')['fuente_datos'] === 'db' ? 'Simulado' : 'Mock');
            $clase = "Caja\\Integracion\\Adaptadores\\{$modo}\\" . self::MAPA[$nombre] . $modo;
            if (!class_exists($clase)) throw new \LogicException("Falta el adaptador {$clase} (puerto '{$nombre}' aún no conectado).");
            self::$inst[$nombre] = new $clase();
        }
        return self::$inst[$nombre];
    }
}
