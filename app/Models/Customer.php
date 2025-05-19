<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;


class Customer extends Model
{
    //
    use HasUuids;

    protected $fillable = ['id', 'name', 'phone_number'];

    public function transactions()
    {
        return $this->hasMany(Transaction::class);
    }
}
