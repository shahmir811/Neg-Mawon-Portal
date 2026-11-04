<?php

use App\Mail\ContactFormReceived;
use Illuminate\Support\Facades\Mail;

test('the contact form is emailed to a single inbox outside production', function () {
    Mail::fake();

    $response = $this->post(route('contact.store'), [
        'name' => 'Marie Joseph',
        'email' => 'marie@example.com',
        'phone' => '267-555-0100',
        'message' => 'Do you service Bucks County?',
    ]);

    $response->assertSessionHasNoErrors()
        ->assertRedirect(route('home').'#contact');

    Mail::assertSent(ContactFormReceived::class, function ($mail) {
        return $mail->hasTo('ngmcleaning2026@gmail.com')
            && ! $mail->hasTo('jnguillaume4@gmail.com')
            && ! $mail->hasTo('jldajeune@gmail.com');
    });
});
