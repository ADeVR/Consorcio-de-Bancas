<?php
declare(strict_types=1);
namespace Caja\Core;



/**
 * Entrada HTTP validada en servidor (Anexo B).
 * Reglas: entero+ · dinero (>0) · dinero0 (≥0) · texto:min-max · fecha · ticket · enum:a|b
 * Sufijo «?» = opcional (ausente o vacío → se omite del resultado).
 */
final class Peticion
{
    public static function exigirMetodo(string $metodo): void
    {
        if (($_SERVER['REQUEST_METHOD'] ?? '') !== $metodo) throw new CajaException('PARAMETROS_INVALIDOS');
    }

    /** @return array<string,mixed> */
    public static function validar(array $reglas, ?array $datos = null): array
    {
        $datos ??= self::datos();
        $salida = [];
        foreach ($reglas as $campo => $regla) {
            $opcional = str_ends_with($regla, '?');
            $regla    = rtrim($regla, '?');
            $valor    = $datos[$campo] ?? null;
            if ($valor === null || $valor === '') {
                if ($opcional) continue;
                throw new CajaException(str_starts_with($regla, 'dinero') ? 'MONTO_INVALIDO' : 'PARAMETROS_INVALIDOS', $campo);
            }
            $salida[$campo] = self::aplicar($campo, $regla, $valor);
        }
        return $salida;
    }

    /** @return array<string,mixed> */
    private static function datos(): array
    {
        if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'GET') return $_GET;
        $crudo = file_get_contents('php://input') ?: '';
        if (str_contains($_SERVER['CONTENT_TYPE'] ?? '', 'application/json')) {
            $json = json_decode($crudo, true);
            if (!is_array($json)) throw new CajaException('PARAMETROS_INVALIDOS');
            return $json;
        }
        return $_POST;
    }

    private static function aplicar(string $campo, string $regla, mixed $valor): mixed
    {
        $malo = new CajaException('PARAMETROS_INVALIDOS', $campo);
        [$tipo, $arg] = array_pad(explode(':', $regla, 2), 2, '');

        switch ($tipo) {
            case 'entero+':
                if ((!is_int($valor) && !(is_string($valor) && ctype_digit($valor))) || (int) $valor < 1) throw $malo;
                return (int) $valor;

            case 'dinero':
            case 'dinero0':
                if (!is_string($valor) && !is_int($valor)) throw new CajaException('MONTO_INVALIDO', $campo);
                $texto = (string) $valor;
                $d     = \Caja\Core\Dinero::desdeTexto($texto);          // lanza MONTO_INVALIDO si el formato es malo
                $max   = \Caja\Core\Dinero::desdeTexto((string) Config::get('app')['importe_maximo']);
                if (($tipo === 'dinero' && !$d->esPositivo()) || $d->mayorQue($max)) throw new CajaException('MONTO_INVALIDO', $campo);
                return trim($texto);

            case 'texto':
                if (!is_string($valor) || !preg_match('/^(\d+)-(\d+)$/', $arg, $m)) throw $malo;
                $t = trim($valor);
                $n = mb_strlen($t);
                if ($n < (int) $m[1] || $n > (int) $m[2]) throw $malo;
                return $t;

            case 'fecha':
                if (!is_string($valor) || !preg_match('/^(\d{4})-(\d{2})-(\d{2})$/', $valor, $m)
                    || !checkdate((int) $m[2], (int) $m[3], (int) $m[1])) throw $malo;
                return $valor;

            case 'ticket':
                if (!is_string($valor) || !preg_match('/^[A-Za-z0-9\-]{1,30}$/', $valor)) throw $malo;
                return $valor;

            case 'enum':
                if (!is_string($valor) || !in_array($valor, explode('|', $arg), true)) throw $malo;
                return $valor;
        }
        throw new \LogicException("Regla de validación desconocida: {$regla}");
    }
}
