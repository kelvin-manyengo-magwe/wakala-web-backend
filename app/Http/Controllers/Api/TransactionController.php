<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\{
    Customer,
    Device,
    OriginalSms,
    Transaction,
    TransactionType
};
use App\Events\TransactionSynced;

class TransactionController extends Controller
{
    public function sync(Request $request)
    {
        $request->validate([
            'device_id' => 'required|uuid',
            'transactions' => 'required|array',
            'transactions.*.customer_name' => 'required|string',
            'transactions.*.customer_no' => 'required|string',
            'transactions.*.ref_no' => 'required|string',
            'transactions.*.date' => 'required|date',
            'transactions.*.amount' => 'required|numeric',
            'transactions.*.type' => 'required|string',
            'transactions.*.commission' => 'required|numeric',
            'transactions.*.float' => 'required|numeric',
            'transactions.*.raw' => 'required'
        ]);

        $syncedCount = 0;

        foreach ($request->transactions as $txn) {
            // Skip if transaction already exists
            if (Transaction::where('ref_no', $txn['ref_no'])->exists()) {
                continue;
            }

            // Normalize transaction type
            $typeName = $this->normalizeType($txn['type']);

            // Get or create related records
            $device = Device::firstOrCreate(
                ['id' => $request->device_id],
                ['name' => 'Mobile Device']
            );

            $customer = Customer::firstOrCreate(
                ['phone_number' => $txn['customer_no']],
                ['name' => $txn['customer_name']]
            );

            $type = TransactionType::firstOrCreate(
                ['name' => $typeName]
            );

            $sms = OriginalSms::create([
                'raw_sms' => is_string($txn['raw']) ? $txn['raw'] : json_encode($txn['raw'])
            ]);

            // Create the transaction
            $transaction = Transaction::create([
                'device_id' => $device->id,
                'customer_id' => $customer->id,
                'sms_id' => $sms->id,
                'type_id' => $type->id,
                'ref_no' => $txn['ref_no'],
                'date' => $txn['date'],
                'amount' => $txn['amount'],
                'commission' => $txn['commission'],
                'float' => $txn['float'],
                'raw' => json_encode($txn),
                'createdAt' => now()
            ]);

            broadcast(new TransactionSynced($transaction));
            $syncedCount++;
        }

        return response()->json([
            'status' => 'success',
            'message' => "$syncedCount transactions synced"
        ]);
    }

    private function normalizeType($type)
    {
        $type = strtolower($type);
        if ($type === 'weka') return 'deposit';
        if ($type === 'toa') return 'withdrawal';
        return $type;
    }
}
