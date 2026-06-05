<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class HistoryTopic extends Model {
    public function era(): BelongsTo {
        return $this->belongsTo(HistoryEra::class, 'history_era_id');
    }
}
