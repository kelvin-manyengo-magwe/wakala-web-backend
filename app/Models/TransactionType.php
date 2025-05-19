<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class TransactionType extends Model
{
    //
    use HasUuids;

    protected $fillable = ['id', 'name'];

    public function transactions()
    {
        return $this->hasMany(Transaction::class, 'type_id');
    }
}
