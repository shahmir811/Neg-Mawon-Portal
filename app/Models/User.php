<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Enums\Role;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * @property int $id
 * @property string $name
 * @property string $email
 * @property Carbon|null $email_verified_at
 * @property string $password
 * @property Role $role
 * @property string|null $two_factor_secret
 * @property string|null $two_factor_recovery_codes
 * @property Carbon|null $two_factor_confirmed_at
 * @property string|null $remember_token
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property Carbon|null $deleted_at
 */
#[Fillable(['name', 'email', 'password', 'role'])]
#[Hidden(['password', 'two_factor_secret', 'two_factor_recovery_codes', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable, SoftDeletes;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'role' => Role::class,
        ];
    }

    /**
     * Get the user's initials
     */
    public function initials(): string
    {
        $initials = Str::initials($this->name, true);

        return Str::length($initials) > 1
            ? Str::substr($initials, 0, 1).Str::substr($initials, -1)
            : $initials;
    }

    public function isAdmin(): bool
    {
        return $this->role === Role::Admin;
    }

    public function isCleaner(): bool
    {
        return $this->role === Role::Cleaner;
    }

    public function isCustomer(): bool
    {
        return $this->role === Role::Customer;
    }

    /**
     * The user's own profile photo, for internal UI (sidebar, admin lists) —
     * unrelated to the customer-facing privacy rule in CleaningJob, which
     * governs what a *customer* may see about their assigned cleaner.
     */
    public function avatarUrl(): ?string
    {
        $photoPath = $this->cleanerProfile?->photo_path;

        return $photoPath ? Storage::url($photoPath) : null;
    }

    /** @return HasOne<CleanerProfile, $this> */
    public function cleanerProfile(): HasOne
    {
        return $this->hasOne(CleanerProfile::class);
    }

    /** @return HasOne<CustomerProfile, $this> */
    public function customerProfile(): HasOne
    {
        return $this->hasOne(CustomerProfile::class);
    }

    /** @return HasMany<CleaningJob, $this> */
    public function jobsAsCustomer(): HasMany
    {
        return $this->hasMany(CleaningJob::class, 'customer_id');
    }

    /** @return HasMany<CleaningJob, $this> */
    public function jobsAsCleaner(): HasMany
    {
        return $this->hasMany(CleaningJob::class, 'cleaner_id');
    }
}
