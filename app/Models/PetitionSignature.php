<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model as BaseModel;
use Illuminate\Support\Str;

class PetitionSignature extends BaseModel {
    public $incrementing = false;
    protected $keyType   = 'string';

    protected $fillable = [
        'petition_id', 'first_name', 'last_name', 'email',
        'city', 'state', 'zip_code', 'phone', 'custom_message',
    ];

    protected static function booted(): void {
        static::creating(function ($model) {
            $model->id = (string) Str::uuid();
        });
    }
}
