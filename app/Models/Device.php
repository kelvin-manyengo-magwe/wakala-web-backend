<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Relations\BelongsTo;





class Device extends Model
{
    //
    use HasUuids;

    protected $fillable = ['id', 'name', 'shop_id'];

    public function transactions()
    {
        return $this->hasMany(Transaction::class);
    }

    public function shop(): BelongsTo
      {
          return $this->belongsTo(Shop::class);
      }

      public function getDeviceIdDisplayAttribute(): string
          {
              return ($this->name ? $this->name . ' (' : '') . $this->id . ($this->name ? ')' : '');
          }
          
}
