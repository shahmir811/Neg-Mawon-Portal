<?php

namespace App\Actions\Fortify;

use App\Concerns\PasswordValidationRules;
use App\Concerns\ProfileValidationRules;
use App\Enums\Role;
use App\Enums\ServiceType;
use App\Models\User;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Laravel\Fortify\Contracts\CreatesNewUsers;

class CreateNewUser implements CreatesNewUsers
{
    use PasswordValidationRules, ProfileValidationRules;

    /**
     * Validate and create a newly registered user.
     *
     * @param  array<string, mixed>  $input
     */
    public function create(array $input): User
    {
        $role = $input['role'] ?? Role::Customer->value;

        Validator::make($input, [
            ...$this->profileRules(),
            'password' => $this->passwordRules(),
            'role' => ['nullable', 'in:'.Role::Customer->value.','.Role::Cleaner->value],
            'phone' => [$role === Role::Cleaner->value ? 'required' : 'nullable', 'string', 'max:20'],
            'photo' => [$role === Role::Cleaner->value ? 'required' : 'nullable', 'image', 'max:5120'],
            'zip_code' => ['nullable', 'string', 'max:10'],
            'service_area' => ['nullable', 'string', 'max:255'],
            'experience' => ['nullable', 'string', 'max:1000'],
            'services_offered' => ['nullable', 'array'],
            'services_offered.*' => [Rule::enum(ServiceType::class)],
            'availability' => ['nullable', 'string', 'max:255'],
            'business_name' => ['nullable', 'string', 'max:255'],
            'insurance_provider' => ['nullable', 'string', 'max:255'],
        ])->validate();

        $user = User::create([
            'name' => $input['name'],
            'email' => $input['email'],
            'password' => $input['password'],
            'role' => Role::from($role),
        ]);

        if ($role === Role::Cleaner->value) {
            $user->cleanerProfile()->create([
                'phone' => $input['phone'],
                'zip_code' => $input['zip_code'] ?? null,
                'photo_path' => $input['photo']->store('cleaners', 'public'),
                'service_area' => $input['service_area'] ?? null,
                'experience' => $input['experience'] ?? null,
                'services_offered' => $input['services_offered'] ?? [],
                'availability' => $input['availability'] ?? null,
                'business_name' => $input['business_name'] ?? null,
                'insurance_provider' => $input['insurance_provider'] ?? null,
            ]);
        } else {
            $user->customerProfile()->create();
        }

        return $user;
    }
}
