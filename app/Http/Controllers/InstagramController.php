<?php

namespace App\Http\Controllers;

use App\Models\InstagramItem;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class InstagramController extends Controller
{
    public function index(Request $request)
    {
        $items = InstagramItem::query()
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

        $exists = InstagramItem::query()->where('url', $data['url'])->exists();

        if ($exists) {
            return back()->with('instagram_status', 'already')->withInput();
        }

        $last = InstagramItem::query()->max('sort_order') ?? 0;
        InstagramItem::create([
            'url' => $data['url'],
            'sort_order' => $last + 1,
        ]);

        return back()->with('instagram_status', 'added');
    }

    public function destroy(int $id)
    {
        InstagramItem::query()->findOrFail($id)->delete();

        return back()->with('instagram_status', 'removed');
    }
}