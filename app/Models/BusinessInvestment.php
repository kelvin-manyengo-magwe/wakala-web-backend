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
}
