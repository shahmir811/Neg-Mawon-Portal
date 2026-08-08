<?php

namespace App\Enums;

enum PropertyType: string
{
    case Residential = 'residential';
    case Church = 'church';
    case Restaurant = 'restaurant';
    case Office = 'office';
    case Retail = 'retail';
    case Other = 'other';

    public function label(): string
    {
        return match ($this) {
            self::Residential => 'Residential (home)',
            self::Church => 'Church',
            self::Restaurant => 'Restaurant',
            self::Office => 'Office',
            self::Retail => 'Retail',
            self::Other => 'Other',
        };
    }
}
