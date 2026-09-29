<?php

namespace App\Http\Controllers;

use App\Models\CustomerActivity;
use App\Services\ActivityTracker;
use Illuminate\Http\Request;

/**
 * Public beacon endpoint for client-side analytics events that the
 * server cannot observe directly (e.g. a shopper selecting a size/color
 * variant on the product page). Strictly whitelisted: only product views
 * with an optional variant may be reported here, and only anonymous
 * activity data is ever stored.
 */
class ActivityTrackController extends Controller
{
    public function store(Request $request)
    {
        $data = $request->validate([
            'action' => 'required|string|in:product_viewed',
            'product_id' => 'required|integer|exists:products,id',
            'size' => 'nullable|string|max:40',
            'color' => 'nullable|string|max:60',
        ]);

        // Base product views are recorded server-side; this endpoint only
        // accepts views that carry an explicit variant selection.
        if (empty($data['size']) && empty($data['color'])) {
            return response()->json(['success' => true, 'skipped' => true]);
        }

        ActivityTracker::track(CustomerActivity::VIEWED, (int) $data['product_id'], [
            'size' => $data['size'] ?? null,
            'color' => $data['color'] ?? null,
        ]);

        return response()->json(['success' => true]);
    }
}