<?php

namespace App\Enums;

enum PropertyType: string
{
    case Residential = 'residential';
    case Commercial = 'commercial';
    case Church = 'church';
    case Restaurant = 'restaurant';
    case Office = 'office';
    case Retail = 'retail';
    case Other = 'other';

    public function label(): string
    {
        return match ($this) {
            self::Residential => 'Residential (home)',
            self::Commercial => 'Commercial (business)',
            self::Church => 'Church',
            self::Restaurant => 'Restaurant',
            self::Office => 'Office',
            self::Retail => 'Retail',
            self::Other => 'Other',
        };
    }
}
