<?php

namespace App;

enum Area: string
{
    case CUARTO_DE_MAQUINAS = 'Cuarto de maquinas';
    case TINTORERIA = 'Tintoreria';
    case LAVANDERIA_INSTITUCIONAL = 'Lavanderia Institucional';

    public function label(): string
    {
        return match ($this) {
            self::TINTORERIA => 'Tintoreria',
            self::LAVANDERIA_INSTITUCIONAL => 'Lavanderia',
            self::CUARTO_DE_MAQUINAS => 'Cuarto de maquinas'
        };
    }
}
