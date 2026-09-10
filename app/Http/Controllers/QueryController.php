<?php

namespace App\Http\Controllers;

use App\Models\Query;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class QueryController extends Controller
{
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'order_id'      => ['nullable', 'string', 'max:50'],
            'name'          => ['required', 'string', 'max:255'],
            'phone_number'  => ['required', 'string', 'regex:/^[0-9]{10}$/'],
            'email'         => ['required', 'email', 'max:255'],
            'query_status'  => ['required', 'string', 'max:100'],
            'query_subject' => ['required', 'string', 'max:255'],
            'message'       => ['nullable', 'string', 'max:5000'],
        ]);

        Query::create([
            ...$validated,
            'status' => Query::STATUS_NEW,
        ]);

        return redirect()->route('contact')->with('query_submitted', true);
    }

    public function index(): View
    {
        $queries = Query::query()->latest()->get();

        return view('seller.queries', compact('queries'));
    }

    public function updateStatus(Request $request, int $id): RedirectResponse
    {
        $query = Query::findOrFail($id);

        $validated = $request->validate([
            'status' => ['required', 'in:new,resolved'],
        ]);

        $query->update(['status' => $validated['status']]);

        return redirect()->route('queries')->with('status_updated', true);
    }
}