<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class SiteVisit extends Model
{
    protected $fillable = ['visit_date', 'visits'];

    public static function trackVisit(): void
    {
        $today = now()->toDateString();

        DB::table('site_visits')->updateOrInsert(
            ['visit_date' => $today],
            [
                'visits' => DB::raw('visits + 1'),
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );
    }

    public static function stats(): array
    {
        static $cached = null;

        if ($cached !== null) {
            return $cached;
        }

        $total = (int) self::query()->sum('visits');
        $today = self::query()->whereDate('visit_date', now()->toDateString())->value('visits') ?? 0;

        return $cached = [
            'today' => (int) $today,
            'total' => $total,
        ];
    }
}