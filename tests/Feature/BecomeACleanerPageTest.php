<?php

test('the become a cleaner page is accessible and links to cleaner registration', function () {
    $response = $this->get(route('become-a-cleaner'));

    $response->assertOk()
        ->assertSee('Become a Cleaning Professional')
        ->assertSee(route('register', ['role' => 'cleaner']), false);
});

test('the homepage links to the become a cleaner page', function () {
    $response = $this->get(route('home'));

    $response->assertOk()->assertSee(route('become-a-cleaner'), false);
});
