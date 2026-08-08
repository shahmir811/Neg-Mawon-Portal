<?php

namespace App\Enums;

enum CleaningType: string
{
    case Deep = 'deep';
    case Soft = 'soft';

    public function label(): string
    {
        return match ($this) {
            self::Deep => 'Deep Cleaning',
            self::Soft => 'Standard / Soft Cleaning',
        };
    }
}
