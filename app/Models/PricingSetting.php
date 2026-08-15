<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable([
    'base_flat_fee',
    'per_bedroom_rate',
    'per_bathroom_rate',
    'base_rates_by_size',
    'pet_fee_per_pet',
    'laundry_fee',
    'deep_cleaning_surcharge',
    'frequency_discounts',
])]
class PricingSetting extends Model
{
    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'base_flat_fee' => 'decimal:2',
            'per_bedroom_rate' => 'decimal:2',
            'per_bathroom_rate' => 'decimal:2',
            'base_rates_by_size' => 'array',
            'pet_fee_per_pet' => 'decimal:2',
            'laundry_fee' => 'decimal:2',
            'deep_cleaning_surcharge' => 'decimal:2',
            'frequency_discounts' => 'array',
        ];
    }

    /**
     * Single editable row of pricing rules, tuned from the admin Pricing
     * page (admin.pricing). Created with placeholder defaults the first
     * time it's read, since James hasn't provided a rate sheet yet —
     * editing the numbers never requires a code change.
     */
    public static function current(): self
    {
        return static::query()->firstOrCreate(['id' => 1], [
            'base_flat_fee' => 75,
            'per_bedroom_rate' => 15,
            'per_bathroom_rate' => 10,
            'base_rates_by_size' => [
                'Under 1,000 sq ft' => 80,
                '1,000-3,000 sq ft' => 130,
                '3,000-5,000 sq ft' => 200,
                '5,000+ sq ft' => 280,
                'Not sure / prefer to discuss' => 130,
            ],
            'pet_fee_per_pet' => 10,
            'laundry_fee' => 20,
            'deep_cleaning_surcharge' => 30,
            'frequency_discounts' => [
                'one_time' => 0,
                'weekly' => 15,
                'bi_weekly' => 10,
                'monthly' => 5,
            ],
        ]);
    }
}
