<?php

use App\Enums\CommercialBusinessType;
use App\Enums\QuoteType;
use App\Models\QuoteRequest;
use App\Models\User;
use Livewire\Livewire;

test('a customer cannot access the quote requests page', function () {
    $user = User::factory()->customer()->create();
    $this->actingAs($user);

    $this->get(route('admin.quote-requests'))->assertForbidden();
});

test('a cleaner cannot access the quote requests page', function () {
    $user = User::factory()->cleaner()->create();
    $this->actingAs($user);

    $this->get(route('admin.quote-requests'))->assertForbidden();
});

test('admin sees every submitted quote request', function () {
    $admin = User::factory()->admin()->create();

    QuoteRequest::factory()->residential()->create(['name' => 'Marie Joseph']);
    QuoteRequest::factory()->commercial()->create(['name' => 'Jane Office Manager']);

    $this->actingAs($admin);

    $this->get(route('admin.quote-requests'))
        ->assertOk()
        ->assertSee('Marie Joseph')
        ->assertSee('Jane Office Manager');
});

test('admin can filter quote requests by type', function () {
    $admin = User::factory()->admin()->create();

    QuoteRequest::factory()->residential()->create(['name' => 'Marie Joseph']);
    QuoteRequest::factory()->commercial()->create(['name' => 'Jane Office Manager']);

    $this->actingAs($admin);

    Livewire::test('pages::admin.quote-requests')
        ->set('typeFilter', QuoteType::Residential->value)
        ->assertSee('Marie Joseph')
        ->assertDontSee('Jane Office Manager');

    Livewire::test('pages::admin.quote-requests')
        ->set('typeFilter', QuoteType::Commercial->value)
        ->assertSee('Jane Office Manager')
        ->assertDontSee('Marie Joseph');
});

test('admin can view the full detail of a quote request', function () {
    $admin = User::factory()->admin()->create();

    $quoteRequest = QuoteRequest::factory()->commercial()->create([
        'name' => 'Jane Office Manager',
        'business_type' => CommercialBusinessType::Office,
        'property_details' => 'A very specific office cleaning need',
    ]);

    $this->actingAs($admin);

    Livewire::test('pages::admin.quote-requests')
        ->call('show', $quoteRequest->id)
        ->assertSee('A very specific office cleaning need')
        ->assertSee(CommercialBusinessType::Office->label());
});
