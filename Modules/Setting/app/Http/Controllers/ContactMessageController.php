<?php

namespace Modules\Setting\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Modules\Setting\Http\Requests\StoreContactMessageRequest;
use Modules\Setting\Models\ContactMessage;

class ContactMessageController extends Controller
{
    public function store(StoreContactMessageRequest $request): JsonResponse
    {
        ContactMessage::create([
            'is_anonymous' => (bool) $request->input('is_anonymous', false),
            'name' => $request->input('name'),
            'email' => $request->input('email'),
            'mobile' => $request->input('mobile'),
            'subject' => $request->input('subject'),
            'message' => $request->input('message'),
            'status' => 'new',
        ]);

        return response()->json([
            'message' => 'Thank you. Your message has been sent.',
        ], 201);
    }
}
