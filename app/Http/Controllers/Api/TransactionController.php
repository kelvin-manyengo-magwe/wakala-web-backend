<?php
// app/Http/Controllers/Api/TransactionController.php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\{ // Add new models
    Customer, Device, OriginalSms, TransactionType, User,
    AirtelTransaction, HalotelTransaction
};
// Remove: use App\Models\Transaction; // If fully replacing
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
// use App\Events\TransactionSynced; // Consider new event types

class TransactionController extends Controller
{
    public function sync(Request $request)
    {
        $authenticatedUser = $request->user();
        $syncedCount = 0;
        $skippedCount = 0;

        foreach ($request->transactions as $txnData) {
            $mno = strtolower(trim($txnData['mno'] ?? 'unknown'));
            $refNo = $txnData['ref_no'];
            $transactionExists = false;
            $targetModel = null;

            if ($mno === 'airtel') {
                $transactionExists = AirtelTransaction::where('ref_no', $refNo)->exists();
                $targetModel = new AirtelTransaction();
            } elseif ($mno === 'halotel') {
                $transactionExists = HalotelTransaction::where('ref_no', $refNo)->exists();
                $targetModel = new HalotelTransaction();
            } else {
                Log::warning("Unsupported MNO '{$mno}' for ref '{$refNo}'. Skipping.");
                $skippedCount++;
                continue;
            }

            if ($transactionExists) {
                $skippedCount++;
                continue;
            }

            // Get or create related records
            $device = Device::firstOrCreate(['id' => $request->device_id], ['name' => 'Mobile Device ' . Str::limit($request->device_id, 8)]);
            $customer = Customer::firstOrCreate(['phone_number' => $txnData['customer_no']], ['name' => $txnData['customer_name']]);
            $typeName = $this->normalizeType($txnData['type']);
            $type = TransactionType::firstOrCreate(['name' => $typeName]);
            $sms = OriginalSms::create(['raw_sms' => is_string($txnData['raw']) ? $txnData['raw'] : json_encode($txnData['raw'])]);

            $payload = [
                'device_id' => $device->id,
                'customer_id' => $customer->id,
                'sms_id' => $sms->id,
                'type_id' => $type->id,
                'user_id' => $authenticatedUser->id, // ID of the logged-in Wakala
                'ref_no' => $refNo,
                'date' => $txnData['date'],
                'amount' => $txnData['amount'],
                'commission' => $txnData['commission'] ?? 0.00,
                'float_balance' => $txnData['float'] ?? 0.00, // Mapped 'float' to 'float_balance'
                'raw_payload' => json_encode($txnData), // Renamed from 'raw'
                'processed_at' => $txnData['createdAt'] ?? now(), // mobile's 'createdAt' is our 'processed_at'
            ];

            // Set UUID if model uses HasUuids and 'id' is fillable for it
            // If primary key is auto-incrementing or DB generated UUID, this is not needed.
            // But since HasUuids is used and primary key is uuid in migrations, model should handle it.
            // If your models expect ID in fillable, ensure this is included. For HasUuids, Eloquent handles it.

            $newTransaction = $targetModel->create($payload);
            // broadcast(new TransactionSynced($newTransaction)); // Adapt event if needed

            $syncedCount++;
        }

        $message = "$syncedCount transactions synced successfully.";
        if ($skippedCount > 0) $message .= " $skippedCount transactions were skipped (already exist or unsupported MNO).";

        return response()->json(['status' => 'success', 'message' => $message, 'user_id' => $authenticatedUser->id]);
    }

    private function normalizeType($type)
    {
        $type = strtolower(trim($type));
        if (in_array($type, ['weka', 'deposit', 'kuweka'])) return 'deposit';
        if (in_array($type, ['toa', 'withdrawal', 'kutoa'])) return 'withdrawal';
        return $type;
    }
}
