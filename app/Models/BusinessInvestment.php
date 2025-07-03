<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids; // For UUIDs
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;



class BusinessInvestment extends Model
{
    use HasFactory, HasUuids; // Add HasUuids if you use UUIDs for primary key

    protected $fillable = [
        'initial_investment_amount',
        'investment_date',
        'notes',
    ];

    protected $casts = [

        'initial_investment_amount' => 'decimal:2',
        'investment_date' => 'date',
    ];



    // If using UUIDs as primary key and it's not 'id', or not auto-incrementing:
    // public $incrementing = false;
    // protected $keyType = 'string';

    public function shopsFunded(): HasMany // Changed name for clarity
    {
        return $this->hasMany(Shop::class, 'business_investment_id', 'id');
    }

    public function getRemainingAmountAttribute(): float
      {
          // Ensure the shops relationship is loaded for calculation. This is efficient.
          $this->loadMissing('shopsFunded');

          // Calculate the total cash and float allocated to shops already funded by this investment.
          $totalCashAllocated = $this->shopsFunded->sum('initial_cash_on_hand');

          // This relies on the 'total_initial_float' accessor existing on your Shop model.
          // It sums the 'initial_float_allocated' from the JSON field for each shop.
          $totalFloatAllocated = $this->shopsFunded->sum('total_initial_float');

          $totalAllocated = $totalCashAllocated + $totalFloatAllocated;

          return (float) $this->initial_investment_amount - $totalAllocated;
      }


}
