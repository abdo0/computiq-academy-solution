<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\ContactFormRequest;
use App\Models\ContactMessage;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;

class ContactController extends Controller
{
    /**
     * Store a new contact message.
     */
    public function store(ContactFormRequest $request): JsonResponse
    {
        try {
            $contactMessage = ContactMessage::create([
                'name' => $request->validated('name'),
                'email' => $request->validated('email'),
                'subject' => $request->validated('subject'),
                'message' => $request->validated('message'),
            ]);

            Log::info('Contact message received', [
                'id' => $contactMessage->id,
                'email' => $contactMessage->email,
            ]);

            return response()->success(
                ['message' => __('Message sent successfully.')],
                __('Your message has been sent successfully. We will get back to you soon.')
            );
        } catch (\Exception $e) {
            Log::error('Failed to store contact message', [
                'error' => $e->getMessage(),
                'data' => $request->all(),
            ]);

            return response()->error(
                __('Failed to send message. Please try again later.'),
                500
            );
        }
    }
}

