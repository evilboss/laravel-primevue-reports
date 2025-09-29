<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Market extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'code',
        'timezone',
        'active',
    ];

    protected $casts = [
        'active' => 'boolean',
    ];

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'user_market');
    }

    public function jobs(): HasMany
    {
        return $this->hasMany(LogServiceTitanJob::class);
    }

    public function events(): HasMany
    {
        return $this->hasMany(LogEvent::class);
    }

    public function scopeActive($query)
    {
        return $query->where('active', true);
    }
}
