<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;


class Shop extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'name',
        'location',
        'user_id', // Owner/Manager of the shop
        'initial_cash_on_hand',
        'mno_initial_allocations',
        'is_active',
    ];

    protected $casts = [
        'initial_cash_on_hand' => 'decimal:2',
        'mno_initial_allocations' => 'array', // Cast JSON to array
        'is_active' => 'boolean',
    ];

    /**
     * The user who owns or primarily manages this shop.
     */
    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function assignedWakalas(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'shop_user', 'shop_id', 'user_id')
                    ->withTimestamps(); // If your pivot table has timestamps
    }

    /**
     * Daily reports for this shop.
     */
    public function dailyReports(): HasMany
    {
        return $this->hasMany(DailyShopReport::class);
    }

    // You might add accessors to get specific MNO initial float or total initial float
    public function getTotalInitialFloatAllocatedAttribute(): float
    {
        if (empty($this->mno_initial_allocations))
        {
            return 0;
        }
        return collect($this->mno_initial_allocations)->sum('initial_float');
    }
}
