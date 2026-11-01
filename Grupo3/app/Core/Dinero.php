<?php
declare(strict_types=1);
namespace Caja\Core;

/** Centavos enteros. Único lugar con aritmética de dinero (nunca float). */
final class Dinero
{
    private function __construct(private int $c) {}

    public static function desdeTexto(string $t): self
    {
        $t = trim($t);
        if (!preg_match('/^(\d{1,10})(?:\.(\d{1,2}))?$/', $t, $m)) throw new CajaException('MONTO_INVALIDO');
        return new self(((int) $m[1]) * 100 + (int) str_pad($m[2] ?? '0', 2, '0'));
    }
    public static function centavos(int $c): self { return new self($c); }
    public function sumar(self $o): self    { return new self($this->c + $o->c); }
    public function restar(self $o): self   { return new self($this->c - $o->c); }
    public function menorQue(self $o): bool { return $this->c < $o->c; }
    public function mayorQue(self $o): bool { return $this->c > $o->c; }
    public function igual(self $o): bool    { return $this->c === $o->c; }
    public function esPositivo(): bool      { return $this->c > 0; }
    public function esCero(): bool          { return $this->c === 0; }
    public function valor(): int            { return $this->c; }

    /** "1020.00" (para PDO/JSON); la UI formatea RD$ en JS (core/formato.js). */
    public function aTexto(): string
    {
        $a = abs($this->c);
        return ($this->c < 0 ? '-' : '') . intdiv($a, 100) . '.' . str_pad((string) ($a % 100), 2, '0', STR_PAD_LEFT);
    }
}
