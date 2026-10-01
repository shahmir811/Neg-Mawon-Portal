<?php

namespace App\Http\Controllers;

use App\Mail\ContactFormReceived;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

class ContactController extends Controller
{
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255'],
            'phone' => ['required', 'string', 'max:30'],
            'message' => ['required', 'string', 'max:2000'],
        ]);

        Mail::to([
            'ngmcleaning2026@gmail.com',
            'jnguillaume4@gmail.com',
            'jldajeune@gmail.com',
        ])->send(new ContactFormReceived(
            $validated['name'],
            $validated['email'],
            $validated['phone'],
            $validated['message'],
        ));

        return redirect(route('home').'#contact')->with('status', 'contact-sent');
    }
}
