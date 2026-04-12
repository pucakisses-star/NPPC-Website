<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model as BaseModel;
use Illuminate\Support\Str;

class FormSubmission extends BaseModel {
    public $incrementing = false;
    protected $keyType   = 'string';

    protected $fillable = [
        'form_type',
        'data',
        'status',
    ];

    protected $casts = [
        'data' => 'array',
    ];

    protected static function booted(): void {
        static::creating(function ($model) {
            $model->id = (string) Str::uuid();
        });
    }

    public function getNameAttribute(): ?string {
        $data = $this->data ?? [];

        if (! empty($data['name'])) {
            return $data['name'];
        }

        $first = $data['first_name'] ?? '';
        $last = $data['last_name'] ?? '';

        $fullName = trim($first.' '.$last);

        return $fullName !== '' ? $fullName : null;
    }

    public function getEmailAttribute(): ?string {
        return $this->data['email'] ?? null;
    }

    public function isNew(): bool {
        return $this->status === 'new';
    }

    public function isRead(): bool {
        return $this->status === 'read';
    }

    public function isArchived(): bool {
        return $this->status === 'archived';
    }
}
