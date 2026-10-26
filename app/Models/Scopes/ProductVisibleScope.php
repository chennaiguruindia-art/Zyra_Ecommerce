<?php

namespace App\Models\Scopes;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

/**
 * Global scope that hides products whose seller visibility toggle is OFF.
 *
 * Every buyer-facing query automatically excludes hidden products. Panel
 * code (seller/admin) bypasses it explicitly with withoutGlobalScope().
 */
class ProductVisibleScope implements Scope
{
    public function apply(Builder $builder, Model $model): void
    {
        $builder->where($model->qualifyColumn('is_visible'), true);
    }
}
