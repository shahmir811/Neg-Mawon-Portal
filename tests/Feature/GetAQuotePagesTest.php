<?php

test('the residential quote page renders with the residential form', function () {
    $this->get(route('quotes.residential.create'))
        ->assertOk()
        ->assertSee(route('quotes.residential.store'), false)
        ->assertDontSee(route('quotes.commercial.store'), false);
});

test('the commercial quote page renders with the commercial form', function () {
    $this->get(route('quotes.commercial.create'))
        ->assertOk()
        ->assertSee(route('quotes.commercial.store'), false)
        ->assertDontSee(route('quotes.residential.store'), false);
});

test('each quote page links to the other', function () {
    $this->get(route('quotes.residential.create'))
        ->assertOk()
        ->assertSee(route('quotes.commercial.create'), false);

    $this->get(route('quotes.commercial.create'))
        ->assertOk()
        ->assertSee(route('quotes.residential.create'), false);
});
