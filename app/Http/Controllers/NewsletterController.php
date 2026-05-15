<?php

namespace App\Http\Controllers;

use App\Models\Subscriber;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;

class NewsletterController extends Controller
{
    public function __invoke(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'email' => ['required', 'email:rfc', 'max:255'],
        ]);

        Subscriber::updateOrCreate(
            ['email' => $validated['email']],
            [
                'source' => 'bento-home',
                'subscribed_at' => now(),
            ],
        );

        return back()->with('newsletter_status', 'SIGNAL RECIBIDA / suscripcion guardada en SQLite');
    }
}
