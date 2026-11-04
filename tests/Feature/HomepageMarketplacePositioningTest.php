<?php

test('the homepage offers both the find-a-professional and become-a-partner paths', function () {
    $this->get(route('home'))
        ->assertOk()
        ->assertSee('Find a Cleaning Professional')
        ->assertSee('Become a Cleaning Partner');
});

test('the homepage links to both the residential and commercial quote pages', function () {
    $this->get(route('home'))
        ->assertOk()
        ->assertSee(route('quotes.residential.create'), false)
        ->assertSee(route('quotes.commercial.create'), false);
});

test('the homepage lists all six commercial service categories', function () {
    $this->get(route('home'))
        ->assertOk()
        ->assertSee('Office Cleaning')
        ->assertSee('Retail Cleaning')
        ->assertSee('Church &amp; Nonprofit Cleaning', false)
        ->assertSee('Apartment Common Areas')
        ->assertSee('Post-Construction Cleaning')
        ->assertSee('Property Management Contracts');
});
