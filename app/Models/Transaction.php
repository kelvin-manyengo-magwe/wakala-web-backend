<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;


class Transaction extends Model
{
  use HasUuids;

  protected $fillable = [
      'id',
      'device_id',
      'customer_id',
      'type_id',
      'ref_no',
      'date',
      'amount',
      'commission',
      'float',
      'raw',
      'createdAt',
  ];

  protected $casts = [
      'date' => 'datetime',
      'createdAt' => 'datetime',
  ];

      public function device()
      {
          return $this->belongsTo(Device::class);
      }

      public function customer()
      {
          return $this->belongsTo(Customer::class);
      }

      public function type()
      {
          return $this->belongsTo(TransactionType::class, 'type_id');
      }

      public function sms()
      {
          return $this->belongsTo(OriginalSms::class, 'sms_id');
      }

}
