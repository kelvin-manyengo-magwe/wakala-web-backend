<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DailyShopReport extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'shop_id',
        'report_date',
        'total_deposits_amount',
        'total_withdrawals_amount',
        'total_commission_earned',
        'opening_cash_on_hand',
        'closing_cash_on_hand',
        'mno_float_balances', // Store as JSON: {"airtel": {"opening": X, "closing": Y}, ...}
        'calculated_daily_profit',
        'submitted_by_user_id',
        'notes',
    ];

    protected $casts = [
        'report_date' => 'date',
        'total_deposits_amount' => 'decimal:2',
        'total_withdrawals_amount' => 'decimal:2',
        'total_commission_earned' => 'decimal:2',
        'opening_cash_on_hand' => 'decimal:2',
        'closing_cash_on_hand' => 'decimal:2',
        'mno_float_balances' => 'array', // Cast JSON to array
        'calculated_daily_profit' => 'decimal:2',
    ];

    public function shop(): BelongsTo
    {
        return $this->belongsTo(Shop::class);
    }

    public function submittedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'submitted_by_user_id');
    }

    // Accessor for total opening float for the day
    public function getTotalOpeningFloatAttribute(): float
    {
        if (empty($this->mno_float_balances)) return 0;
        return collect($this->mno_float_balances)->sum(fn($mno) => $mno['opening'] ?? 0);
    }

    // Accessor for total closing float for the day
    public function getTotalClosingFloatAttribute(): float
    {
        if (empty($this->mno_float_balances)) return 0;
        return collect($this->mno_float_balances)->sum(fn($mno) => $mno['closing'] ?? 0);
    }
}
