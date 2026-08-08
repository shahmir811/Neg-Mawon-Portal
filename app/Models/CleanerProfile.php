<?php

namespace App\Models;

use App\Enums\AgreementStatus;
use App\Enums\SubscriptionPlan;
use App\Enums\SubscriptionStatus;
use Database\Factories\CleanerProfileFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'user_id',
    'phone',
    'photo_path',
    'agreement_photo_path',
    'agreement_signed',
    'subscription_plan',
    'subscription_status',
    'stripe_id',
    'stripe_status',
    'next_renewal_at',
])]
class CleanerProfile extends Model
{
    /** @use HasFactory<CleanerProfileFactory> */
    use HasFactory;

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'agreement_signed' => 'boolean',
            'subscription_plan' => SubscriptionPlan::class,
            'subscription_status' => SubscriptionStatus::class,
            'next_renewal_at' => 'datetime',
        ];
    }

    /** @return BelongsTo<User, $this> */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /** @return HasMany<CleaningJob, $this> */
    public function jobs(): HasMany
    {
        return $this->hasMany(CleaningJob::class, 'cleaner_id', 'user_id');
    }

    /**
     * Derived from the two existing columns rather than a stored status —
     * a photo present but not yet signed off means the cleaner submitted it
     * and it's awaiting James's review (AGENTS.md Section 6).
     */
    public function agreementStatus(): AgreementStatus
    {
        return match (true) {
            ! $this->agreement_photo_path => AgreementStatus::NotSubmitted,
            $this->agreement_signed => AgreementStatus::Approved,
            default => AgreementStatus::Pending,
        };
    }
}
