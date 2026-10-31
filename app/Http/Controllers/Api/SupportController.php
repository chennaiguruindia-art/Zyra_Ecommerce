<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\ChatBotController;
use App\Http\Controllers\Controller;
use App\Models\CustomerActivity;
use App\Models\Query;
use App\Services\ActivityTracker;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SupportController extends Controller
{
    /**
     * Submit customer inquiry / query ticket.
     */
    public function contact(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'order_id' => ['nullable', 'string', 'max:50'],
            'name' => ['required', 'string', 'max:255'],
            'phone_number' => ['required', 'string', 'regex:/^[0-9]{10}$/'],
            'email' => ['required', 'email', 'max:255'],
            'query_status' => ['nullable', 'string', 'max:100'],
            'query_subject' => ['required', 'string', 'max:255'],
            'message' => ['nullable', 'string', 'max:5000'],
        ]);

        $query = Query::create([
            'order_id' => $validated['order_id'] ?? null,
            'name' => $validated['name'],
            'phone_number' => $validated['phone_number'],
            'email' => $validated['email'],
            'query_status' => $validated['query_status'] ?? 'General Inquiry',
            'query_subject' => $validated['query_subject'],
            'message' => $validated['message'] ?? null,
            'status' => Query::STATUS_NEW,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Your inquiry has been submitted. Our team will contact you shortly.',
            'query_id' => $query->id,
        ], 201);
    }

    /**
     * Mobile chatbot assistant reply.
     */
    public function chatbot(Request $request): JsonResponse
    {
        return app(ChatBotController::class)->reply($request);
    }

    /**
     * Client shopping analytics beacon.
     */
    public function track(Request $request): JsonResponse
    {
        $action = $request->input('action', CustomerActivity::VIEWED);
        $productId = (int) $request->input('product_id', 0);

        if ($productId > 0) {
            ActivityTracker::track($action, $productId);
        }

        return response()->json(['success' => true]);
    }
}
