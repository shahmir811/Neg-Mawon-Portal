<?php

namespace App\Enums;

enum CommercialBusinessType: string
{
    case Office = 'office';
    case Retail = 'retail';
    case ChurchOrNonprofit = 'church_or_nonprofit';
    case ApartmentCommonAreas = 'apartment_common_areas';
    case PostConstruction = 'post_construction';
    case PropertyManagementContract = 'property_management_contract';

    public function label(): string
    {
        return match ($this) {
            self::Office => 'Office',
            self::Retail => 'Retail',
            self::ChurchOrNonprofit => 'Church / Nonprofit',
            self::ApartmentCommonAreas => 'Apartment Common Areas',
            self::PostConstruction => 'Post-Construction',
            self::PropertyManagementContract => 'Property Management Contract',
        };
    }
}
