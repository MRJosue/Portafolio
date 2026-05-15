<?php

namespace App\Http\Controllers;

use App\Mail\ChatLeadReceived;
use App\Models\ChatSession;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class ChatController extends Controller
{
    public function start(Request $request): JsonResponse
    {
        $session = ChatSession::query()->firstOrCreate(
            ['session_key' => $request->string('session_key')->toString() ?: (string) Str::uuid()],
            ['status' => 'open', 'last_message_at' => now()]
        );

        if ($session->messages()->doesntExist()) {
            foreach ($this->botMessages() as $message) {
                $session->messages()->create([
                    'sender' => 'bot',
                    'body' => $message,
                ]);
            }
        }

        return response()->json($this->payload($session->fresh('messages')));
    }

    public function contact(ChatSession $chatSession, Request $request): JsonResponse
    {
        $this->authorizePublicSession($chatSession, $request);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'email' => ['required', 'email', 'max:160'],
            'phone' => ['nullable', 'string', 'max:40'],
            'company' => ['nullable', 'string', 'max:120'],
            'topic' => ['nullable', 'string', 'max:180'],
        ]);

        $chatSession->update([
            ...$validated,
            'status' => 'lead',
            'last_message_at' => now(),
        ]);

        $chatSession->messages()->create([
            'sender' => 'visitor',
            'body' => "Contacto: {$validated['name']} / {$validated['email']}"
                . (($validated['phone'] ?? null) ? " / {$validated['phone']}" : '')
                . (($validated['topic'] ?? null) ? " / {$validated['topic']}" : ''),
        ]);

        $chatSession->messages()->create([
            'sender' => 'bot',
            'body' => 'Gracias. Ya tengo tus datos; desde aqui puedes escribir un mensaje directo y te respondo desde el admin.',
        ]);

        $this->notifyLeadReceived($chatSession->fresh());

        return response()->json($this->payload($chatSession->fresh('messages')));
    }

    public function visitorMessage(ChatSession $chatSession, Request $request): JsonResponse
    {
        $this->authorizePublicSession($chatSession, $request);

        $validated = $request->validate([
            'body' => ['required', 'string', 'max:1000'],
        ]);

        $body = trim($validated['body']);

        $chatSession->messages()->create([
            'sender' => 'visitor',
            'body' => $body,
        ]);

        $this->advanceLeadFlow($chatSession, $body);

        return response()->json($this->payload($chatSession->fresh('messages')));
    }

    public function messages(ChatSession $chatSession, Request $request): JsonResponse
    {
        $this->authorizePublicSession($chatSession, $request);

        return response()->json($this->payload($chatSession->fresh('messages')));
    }

    public function adminReply(ChatSession $chatSession, Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'body' => ['required', 'string', 'max:1000'],
        ]);

        $chatSession->messages()->create([
            'sender' => 'admin',
            'body' => $validated['body'],
        ]);

        $chatSession->update([
            'status' => 'answered',
            'last_message_at' => now(),
        ]);

        return back()->with('admin_status', 'Respuesta enviada al chat del cliente.');
    }

    private function authorizePublicSession(ChatSession $chatSession, Request $request): void
    {
        abort_unless(hash_equals($chatSession->session_key, $request->string('session_key')->toString()), 403);
    }

    private function payload(ChatSession $session): array
    {
        return [
            'session' => [
                'id' => $session->id,
                'session_key' => $session->session_key,
                'status' => $session->status,
                'name' => $session->name,
                'email' => $session->email,
                'phone' => $session->phone,
                'topic' => $session->topic,
                'next_field' => $this->nextField($session),
            ],
            'messages' => $session->messages()
                ->oldest()
                ->get(['id', 'sender', 'body', 'created_at'])
                ->map(fn ($message) => [
                    'id' => $message->id,
                    'sender' => $message->sender,
                    'body' => $message->body,
                    'created_at' => $message->created_at->toISOString(),
                ]),
        ];
    }

    private function botMessages(): array
    {
        return [
            'Hola, soy el asistente de contacto de Josue. Primero, cual es tu nombre?',
        ];
    }

    private function advanceLeadFlow(ChatSession $chatSession, string $body): void
    {
        if (! $chatSession->name) {
            $chatSession->update([
                'name' => $body,
                'last_message_at' => now(),
            ]);

            $chatSession->messages()->create([
                'sender' => 'bot',
                'body' => "Gracias, {$body}. Cual es tu email?",
            ]);

            return;
        }

        if (! $chatSession->email) {
            if (! filter_var($body, FILTER_VALIDATE_EMAIL)) {
                $chatSession->update([
                    'last_message_at' => now(),
                ]);

                $chatSession->messages()->create([
                    'sender' => 'bot',
                    'body' => 'Ese email no parece valido. Me lo puedes escribir de nuevo?',
                ]);

                return;
            }

            $chatSession->update([
                'email' => $body,
                'last_message_at' => now(),
            ]);

            $chatSession->messages()->create([
                'sender' => 'bot',
                'body' => 'Perfecto. Cual es tu telefono?',
            ]);

            return;
        }

        if (! $chatSession->phone) {
            $chatSession->update([
                'phone' => $body,
                'last_message_at' => now(),
            ]);

            $chatSession->messages()->create([
                'sender' => 'bot',
                'body' => 'Gracias. Ahora cuentame brevemente de tu proyecto.',
            ]);

            return;
        }

        if (! $chatSession->topic) {
            $chatSession->update([
                'topic' => $body,
                'status' => 'lead',
                'last_message_at' => now(),
            ]);

            $chatSession->messages()->create([
                'sender' => 'bot',
                'body' => 'Listo, ya tengo tus datos. Preparate un cafe o ve por tu bebida favorita; en unos minutos te atenderemos por aqui.',
            ]);

            $this->notifyLeadReceived($chatSession->fresh());

            return;
        }

        $chatSession->update([
            'status' => 'lead',
            'last_message_at' => now(),
        ]);
    }

    private function nextField(ChatSession $session): ?string
    {
        return match (true) {
            ! $session->name => 'name',
            ! $session->email => 'email',
            ! $session->phone => 'phone',
            ! $session->topic => 'topic',
            default => null,
        };
    }

    private function notifyLeadReceived(ChatSession $chatSession): void
    {
        Mail::to(config('mail.lead_to'))->send(new ChatLeadReceived($chatSession));
    }
}
