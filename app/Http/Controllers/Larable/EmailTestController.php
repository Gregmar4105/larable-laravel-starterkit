<?php

namespace App\Http\Controllers\Larable;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Illuminate\Mail\Message;

/**
 * Email Test Controller
 *
 * Integrates with Mailpit for email testing in the Blade GUI.
 * - Send test emails through the configured SMTP (Mailpit)
 * - Fetch inbox from Mailpit API
 * - View individual email content
 */
class EmailTestController extends Controller
{
    /**
     * Mailpit API base URL.
     */
    protected string $mailpitUrl = 'http://mailpit:8025';

    /**
     * Send a test email.
     */
    public function send(Request $request): JsonResponse
    {
        $request->validate([
            'to' => ['required', 'email'],
            'subject' => ['required', 'string', 'max:255'],
            'body' => ['required', 'string'],
        ]);

        try {
            Mail::raw($request->input('body'), function (Message $message) use ($request) {
                $message->to($request->input('to'))
                    ->subject($request->input('subject'));
            });

            return response()->json([
                'success' => true,
                'message' => 'Test email sent successfully. Check Mailpit inbox.',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to send email: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get inbox messages from Mailpit.
     */
    public function inbox(Request $request): JsonResponse
    {
        try {
            $response = Http::timeout(5)->get($this->mailpitUrl . '/api/v1/messages', [
                'limit' => $request->input('limit', 50),
            ]);

            if ($response->successful()) {
                return response()->json($response->json());
            }

            return response()->json([
                'messages' => [],
                'error' => 'Failed to fetch inbox',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'messages' => [],
                'error' => 'Mailpit is not accessible: ' . $e->getMessage(),
            ]);
        }
    }

    /**
     * Get a specific email message from Mailpit.
     */
    public function message(string $id): JsonResponse
    {
        try {
            $response = Http::timeout(5)->get($this->mailpitUrl . '/api/v1/message/' . $id);

            if ($response->successful()) {
                return response()->json($response->json());
            }

            return response()->json(['error' => 'Message not found'], 404);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Mailpit is not accessible: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Delete all messages in Mailpit.
     */
    public function clear(): JsonResponse
    {
        try {
            Http::timeout(5)->delete($this->mailpitUrl . '/api/v1/messages');

            return response()->json([
                'success' => true,
                'message' => 'Inbox cleared.',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to clear inbox: ' . $e->getMessage(),
            ], 500);
        }
    }
}
