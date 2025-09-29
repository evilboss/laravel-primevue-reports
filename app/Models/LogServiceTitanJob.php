<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LogServiceTitanJob extends Model
{
    use HasFactory;

    protected $table = 'log_service_titan_jobs';

    protected $fillable = [
        'market_id',
        'job_id',
        'booking_date',
        'booking_amount',
        'customer_type',
        'service_type',
        'metadata',
    ];

    protected $casts = [
        'booking_date' => 'date',
        'booking_amount' => 'decimal:2',
        'metadata' => 'array',
    ];

    public function market(): BelongsTo
    {
        return $this->belongsTo(Market::class);
    }

    public function scopeForDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('booking_date', [$startDate, $endDate]);
    }

    public function scopeForMarkets($query, array $marketIds)
    {
        return $query->whereIn('market_id', $marketIds);
    }
}
