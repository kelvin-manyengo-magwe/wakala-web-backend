<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;



class OriginalSms extends Model
{
    //
    use HasUuids;

    protected $table = 'original_sms';

    protected $fillable = [
        'id',
        'raw_sms',
    ];

    public function transaction()
    {
        return $this->hasOne(Transaction::class, 'sms_id');
    }
}
