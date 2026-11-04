<?php

use App\Enums\CommercialBusinessType;
use App\Enums\JobFrequency;
use App\Enums\QuoteType;
use App\Enums\ServiceType;
use App\Mail\CommercialQuoteRequestReceived;
use App\Mail\ResidentialQuoteRequestReceived;
use App\Models\QuoteRequest;
use Illuminate\Support\Facades\Mail;

test('a residential quote request is saved and emailed to the ngm team', function () {
    Mail::fake();

    $response = $this->post(route('quotes.residential.store'), [
        'name' => 'Marie Joseph',
        'email' => 'marie@example.com',
        'phone' => '267-555-0100',
        'zip_code' => '19111',
        'service_type' => ServiceType::HouseCleaning->value,
        'property_details' => '3 bedrooms, 2 bathrooms',
        'frequency' => JobFrequency::Weekly->value,
        'preferred_date' => now()->addWeek()->toDateString(),
        'description' => 'Looking for weekly cleaning.',
    ]);

    $response->assertSessionHasNoErrors()
        ->assertRedirect(route('quotes.residential.create'));

    $quoteRequest = QuoteRequest::sole();

    expect($quoteRequest->type)->toBe(QuoteType::Residential)
        ->and($quoteRequest->name)->toBe('Marie Joseph')
        ->and($quoteRequest->zip_code)->toBe('19111')
        ->and($quoteRequest->service_type)->toBe(ServiceType::HouseCleaning)
        ->and($quoteRequest->business_type)->toBeNull();

    Mail::assertSent(ResidentialQuoteRequestReceived::class, function ($mail) use ($quoteRequest) {
        return $mail->quoteRequest->is($quoteRequest)
            && $mail->hasTo('ngmcleaning2026@gmail.com')
            && $mail->hasTo('jnguillaume4@gmail.com')
            && $mail->hasTo('jldajeune@gmail.com');
    });
});

test('a commercial quote request is saved and emailed to the ngm team', function () {
    Mail::fake();

    $response = $this->post(route('quotes.commercial.store'), [
        'name' => 'Jane Office Manager',
        'email' => 'jane@example.com',
        'phone' => '267-555-0199',
        'business_type' => CommercialBusinessType::Office->value,
        'property_details' => '~5,000 sq ft office, 2 floors',
        'frequency' => JobFrequency::BiWeekly->value,
        'description' => 'Looking for biweekly office cleaning.',
    ]);

    $response->assertSessionHasNoErrors()
        ->assertRedirect(route('quotes.commercial.create'));

    $quoteRequest = QuoteRequest::sole();

    expect($quoteRequest->type)->toBe(QuoteType::Commercial)
        ->and($quoteRequest->business_type)->toBe(CommercialBusinessType::Office)
        ->and($quoteRequest->zip_code)->toBeNull()
        ->and($quoteRequest->service_type)->toBeNull();

    Mail::assertSent(CommercialQuoteRequestReceived::class, function ($mail) use ($quoteRequest) {
        return $mail->quoteRequest->is($quoteRequest);
    });
});

test('a residential quote request requires a zip code and service type', function () {
    $response = $this->post(route('quotes.residential.store'), [
        'name' => 'Marie Joseph',
        'email' => 'marie@example.com',
        'phone' => '267-555-0100',
        'property_details' => '3 bedrooms',
        'frequency' => JobFrequency::Weekly->value,
        'description' => 'Looking for weekly cleaning.',
    ]);

    $response->assertSessionHasErrors(['zip_code', 'service_type']);
});

test('a commercial quote request requires a business type', function () {
    $response = $this->post(route('quotes.commercial.store'), [
        'name' => 'Jane Office Manager',
        'email' => 'jane@example.com',
        'phone' => '267-555-0199',
        'property_details' => '~5,000 sq ft office',
        'frequency' => JobFrequency::BiWeekly->value,
        'description' => 'Looking for biweekly office cleaning.',
    ]);

    $response->assertSessionHasErrors(['business_type']);
});
