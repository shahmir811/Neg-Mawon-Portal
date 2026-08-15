<?php

namespace App\Enums;

enum FloorType: string
{
    case Carpet = 'carpet';
    case HardFloor = 'hard_floor';
    case Mixed = 'mixed';

    public function label(): string
    {
        return match ($this) {
            self::Carpet => 'Carpet',
            self::HardFloor => 'Hardwood / tile / hard floor',
            self::Mixed => 'Mixed (carpet & hard floor)',
        };
    }
}
