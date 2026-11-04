<?php

namespace App\Http\Controllers;

use App\Enums\CommercialBusinessType;
use App\Enums\JobFrequency;
use App\Enums\QuoteType;
use App\Enums\ServiceType;
use App\Mail\CommercialQuoteRequestReceived;
use App\Mail\ResidentialQuoteRequestReceived;
use App\Models\QuoteRequest;
use App\Support\NotificationRecipients;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\Rule;

class QuoteRequestController extends Controller
{
    public function residential(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255'],
            'phone' => ['required', 'string', 'max:30'],
            'zip_code' => ['required', 'string', 'max:10'],
            'service_type' => ['required', Rule::enum(ServiceType::class)],
            'property_details' => ['required', 'string', 'max:500'],
            'frequency' => ['required', Rule::enum(JobFrequency::class)],
            'preferred_date' => ['nullable', 'date', 'after_or_equal:today'],
            'description' => ['required', 'string', 'max:2000'],
        ]);

        $quoteRequest = QuoteRequest::create([
            ...$validated,
            'type' => QuoteType::Residential,
        ]);

        Mail::to(NotificationRecipients::all())->send(new ResidentialQuoteRequestReceived($quoteRequest));

        return redirect()->route('quotes.residential.create')->with('status', 'quote-sent');
    }

    public function commercial(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255'],
            'phone' => ['required', 'string', 'max:30'],
            'business_type' => ['required', Rule::enum(CommercialBusinessType::class)],
            'property_details' => ['required', 'string', 'max:500'],
            'frequency' => ['required', Rule::enum(JobFrequency::class)],
            'description' => ['required', 'string', 'max:2000'],
        ]);

        $quoteRequest = QuoteRequest::create([
            ...$validated,
            'type' => QuoteType::Commercial,
        ]);

        Mail::to(NotificationRecipients::all())->send(new CommercialQuoteRequestReceived($quoteRequest));

        return redirect()->route('quotes.commercial.create')->with('status', 'quote-sent');
    }
}
