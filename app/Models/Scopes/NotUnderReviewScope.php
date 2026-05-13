<?php

namespace App\Models\Scopes;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

/**
 * Hides prisoners marked under_review from public-facing queries.
 * Admin (Filament) calls Prisoner::withUnderReview() to bypass this.
 */
final class NotUnderReviewScope implements Scope {
    public function apply(Builder $builder, Model $model): void {
        $builder->where(function (Builder $q) use ($model) {
            $q->where($model->getTable().'.under_review', false)
                ->orWhereNull($model->getTable().'.under_review');
        });
    }
}
