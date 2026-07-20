<?php

namespace App\Models;

final class QuizResult extends Model {
    protected $casts = [
        'values_scores' => 'array',
        'answers'       => 'array',
    ];
}
