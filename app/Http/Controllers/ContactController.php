<?php

namespace App\Http\Controllers;

use App\Notifications\ContactFormSubmitted;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Notification;

class ContactController extends Controller
{
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255'],
            'message' => ['required', 'string', 'max:2000'],
        ]);

        Notification::route('mail', 'jnguillaume4@gmail.com')
            ->notify(new ContactFormSubmitted(
                $validated['name'],
                $validated['email'],
                $validated['message'],
            ));

        return redirect(route('home').'#contact')->with('status', 'contact-sent');
    }
}
