<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OtpVerification extends Model
{
    protected $fillable = ['phone', 'otp', 'expires_at', 'is_verified'];

    protected function casts(): array
    {
        return [
            'expires_at' => 'datetime',
            'is_verified' => 'boolean',
        ];
    }

    public function isExpired(): bool
    {
        return $this->expires_at->isPast();
    }
}
