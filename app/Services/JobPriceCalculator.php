<?php

namespace App\Services;

use App\Enums\CleaningType;
use App\Enums\JobFrequency;
use App\Enums\PropertyType;
use App\Models\PricingSetting;

/**
 * Placeholder pricing formula until James provides a real rate sheet —
 * every number it uses lives in PricingSetting (admin.pricing) so tuning
 * them never requires a code change.
 */
class JobPriceCalculator
{
    /**
     * @param  array<string, mixed>  $attributes  Same shape as the job's
     *   validated form data: property_type, property_size, bedroom_count,
     *   bathroom_count, cleaning_type, has_pets, pet_count, laundry_addon,
     *   frequency. Enum fields may be passed as their string value or the
     *   enum instance.
     */
    public static function estimate(array $attributes): float
    {
        return self::breakdown($attributes)['total'];
    }

    /**
     * Same inputs as estimate(), but returns every line item that adds up
     * to the total — shown on the admin job page so James can see how a
     * price was built, not just the final number.
     *
     * @param  array<string, mixed>  $attributes
     * @return array{
     *     lines: array<int, array{label: string, amount: float}>,
     *     subtotal: float,
     *     discount_percent: float,
     *     discount_amount: float,
     *     total: float,
     * }
     */
    public static function breakdown(array $attributes): array
    {
        $settings = PricingSetting::current();
        $lines = [];

        $propertyType = self::valueOf($attributes['property_type'] ?? null);
        $bedroomCount = $attributes['bedroom_count'] ?? null;

        if ($propertyType === PropertyType::Residential->value && $bedroomCount !== null) {
            $bathroomCount = (int) ($attributes['bathroom_count'] ?? 0);

            $lines[] = ['label' => 'Base fee', 'amount' => (float) $settings->base_flat_fee];
            $lines[] = [
                'label' => $bedroomCount.' bedroom(s) @ $'.number_format((float) $settings->per_bedroom_rate, 2),
                'amount' => (float) $settings->per_bedroom_rate * (int) $bedroomCount,
            ];
            $lines[] = [
                'label' => $bathroomCount.' bathroom(s) @ $'.number_format((float) $settings->per_bathroom_rate, 2),
                'amount' => (float) $settings->per_bathroom_rate * $bathroomCount,
            ];
        } else {
            $sizeBand = $attributes['property_size'] ?? '';
            $lines[] = [
                'label' => 'Base rate ('.($sizeBand ?: 'default').')',
                'amount' => (float) ($settings->base_rates_by_size[$sizeBand] ?? $settings->base_flat_fee),
            ];
        }

        if (self::valueOf($attributes['cleaning_type'] ?? null) === CleaningType::Deep->value) {
            $lines[] = ['label' => 'Deep cleaning surcharge', 'amount' => (float) $settings->deep_cleaning_surcharge];
        }

        if (! empty($attributes['has_pets'])) {
            $petCount = (int) ($attributes['pet_count'] ?? 0);
            $lines[] = [
                'label' => $petCount.' pet(s) @ $'.number_format((float) $settings->pet_fee_per_pet, 2),
                'amount' => (float) $settings->pet_fee_per_pet * $petCount,
            ];
        }

        if (! empty($attributes['laundry_addon'])) {
            $lines[] = ['label' => 'Laundry add-on', 'amount' => (float) $settings->laundry_fee];
        }

        $subtotal = array_sum(array_column($lines, 'amount'));

        $frequency = self::valueOf($attributes['frequency'] ?? null) ?? JobFrequency::OneTime->value;
        $discountPercent = (float) ($settings->frequency_discounts[$frequency] ?? 0);
        $discountAmount = $subtotal * ($discountPercent / 100);

        return [
            'lines' => $lines,
            'subtotal' => round($subtotal, 2),
            'discount_percent' => $discountPercent,
            'discount_amount' => round($discountAmount, 2),
            'total' => round(max($subtotal - $discountAmount, 0), 2),
        ];
    }

    private static function valueOf(mixed $value): ?string
    {
        return $value instanceof \BackedEnum ? (string) $value->value : $value;
    }
}
