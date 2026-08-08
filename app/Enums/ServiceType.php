<?php

namespace App\Enums;

enum ServiceType: string
{
    case HouseCleaning = 'house_cleaning';
    case JanitorialServices = 'janitorial_services';
    case WindowCleaning = 'window_cleaning';
    case AreaRugsAndSmallCarpets = 'area_rugs_and_small_carpets';
    case MultipleServices = 'multiple_services';
    case Other = 'other';

    public function label(): string
    {
        return match ($this) {
            self::HouseCleaning => 'House Cleaning',
            self::JanitorialServices => 'Janitorial Services',
            self::WindowCleaning => 'Window Cleaning',
            self::AreaRugsAndSmallCarpets => 'Area Rugs & Small Carpets',
            self::MultipleServices => 'Multiple services',
            self::Other => 'Other',
        };
    }
}
