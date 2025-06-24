<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Facades\Storage;


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
        'image_path',
        'business_investment_id'
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
    public function getTotalInitialFloatAttribute(): float
   {
       if (empty($this->mno_initial_allocations) || !is_array($this->mno_initial_allocations)) {
           return 0.0;
       }

       // CORRECTED: Changed key from 'initial_float' to 'initial_float_allocated'
       // to match the key used in the DukaResource form repeater.
       return (float) collect($this->mno_initial_allocations)->sum(function($allocation) {
           return (float) ($allocation['initial_float_allocated'] ?? 0);
       });
   }



    public function devices(): HasMany
    {
        return $this->hasMany(Device::class, 'shop_id', 'id');
    }


    // An accessor to get the full url of the image
        public function getImageUrlAttribute(): ?string
       {
           if ($this->image_path) {
               return Storage::disk('public')->url($this->image_path);
           }
           return null; // Or a placeholder image URL
       }

       /**
     * The specific business investment that initially funded this shop.
     */
    public function fundingInvestment(): BelongsTo
    {
        return $this->belongsTo(BusinessInvestment::class, 'business_investment_id');
    }

}
