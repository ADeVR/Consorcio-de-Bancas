<?php
declare(strict_types=1);
namespace Caja\Core;

/** Error de negocio: lleva solo el código; el mensaje sale de config/errores.xml. */
final class CajaException extends \RuntimeException
{
    public function __construct(public string $codigo, public ?string $campo = null)
    {
        parent::__construct($codigo);
    }
}
