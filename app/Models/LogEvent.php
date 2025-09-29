<?php

namespace App\Models;

use App\Enums\EventType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LogEvent extends Model
{
    use HasFactory;

    protected $fillable = [
        'market_id',
        'session_id',
        'event_type',
        'event_timestamp',
        'event_data',
    ];

    protected $casts = [
        'event_type' => EventType::class,
        'event_timestamp' => 'datetime',
        'event_data' => 'array',
    ];

    public function market(): BelongsTo
    {
        return $this->belongsTo(Market::class);
    }

    public function scopeForDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('event_timestamp', [$startDate, $endDate]);
    }

    public function scopeForMarkets($query, array $marketIds)
    {
        return $query->whereIn('market_id', $marketIds);
    }

    public function scopeForEventType($query, EventType $eventType)
    {
        return $query->where('event_type', $eventType);
    }
}
