<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HalotelTransaction extends Model
{
    use HasFactory, HasUuids;
    protected $table = 'halotel_transactions';
    protected $fillable = [ /* ...all columns from migration except auto-generated ones ... */
        'device_id', 'customer_id', 'sms_id', 'type_id', 'user_id',
        'ref_no', 'date', 'amount', 'commission', 'float_balance',
        'raw_payload', 'processed_at',
    ];
    protected $casts = ['date' => 'datetime', 'processed_at' => 'datetime'];

    public function device() { return $this->belongsTo(Device::class); }
    public function customer() { return $this->belongsTo(Customer::class); }
    public function type() { return $this->belongsTo(TransactionType::class, 'type_id'); }
    public function sms() { return $this->belongsTo(OriginalSms::class, 'sms_id'); }
    public function user() { return $this->belongsTo(User::class); } // Wakala
}
