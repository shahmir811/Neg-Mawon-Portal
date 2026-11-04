<?php

namespace App\Models;

use App\Enums\CommercialBusinessType;
use App\Enums\JobFrequency;
use App\Enums\QuoteType;
use App\Enums\ServiceType;
use Database\Factories\QuoteRequestFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

#[Fillable([
    'type',
    'name',
    'email',
    'phone',
    'zip_code',
    'service_type',
    'business_type',
    'property_details',
    'frequency',
    'preferred_date',
    'description',
])]
class QuoteRequest extends Model
{
    /** @use HasFactory<QuoteRequestFactory> */
    use HasFactory;

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'type' => QuoteType::class,
            'service_type' => ServiceType::class,
            'business_type' => CommercialBusinessType::class,
            'frequency' => JobFrequency::class,
            'preferred_date' => 'date',
        ];
    }
}
