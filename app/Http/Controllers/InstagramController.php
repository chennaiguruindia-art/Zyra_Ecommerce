<?php

namespace App\Http\Controllers;

use App\Models\InstagramItem;
use Illuminate\Http\Request;

class InstagramController extends Controller
{
    public function index(Request $request)
    {
        $items = InstagramItem::query()
            ->where('status', 1)
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get()
            ->map(fn (InstagramItem $item) => [
                'id' => $item->id,
                'url' => $item->url,
            ])
            ->all();

        return view('instagram', ['items' => $items]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'url' => ['required', 'url', 'regex:/^https?:\/\/(www\.)?instagram\.com\/(p|reel|tv)\//i'],
        ]);

        $last = InstagramItem::query()->max('sort_order') ?? 0;

        $previous = InstagramItem::query()->where('url', $data['url'])->first();

        if ($previous && $previous->status === 0) {
            $previous->update(['status' => 1, 'sort_order' => $last + 1]);

            return back()->with('instagram_status', 'added');
        }

        if ($previous) {
            return back()->with('instagram_status', 'already')->withInput();
        }

        InstagramItem::create([
            'url' => $data['url'],
            'sort_order' => $last + 1,
            'status' => 1,
        ]);

        return back()->with('instagram_status', 'added');
    }

    public function destroy(int $id)
    {
        $item = InstagramItem::query()->findOrFail($id);
        $item->update(['status' => 0]);

        return back()->with('instagram_status', 'removed');
    }
}