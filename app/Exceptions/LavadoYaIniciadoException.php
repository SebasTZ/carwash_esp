<?php

namespace App\Exceptions;

class LavadoYaIniciadoException extends LavadoException
{
    public function __construct(int $lavadoId)
    {
        parent::__construct(
            "No se puede modificar el lavado #{$lavadoId} porque ya fue iniciado."
        );
    }
}
