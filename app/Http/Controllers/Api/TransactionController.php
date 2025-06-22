<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Customer;
use App\Models\Device; // Ensure this is your Device model
use App\Models\OriginalSms;
use App\Models\TransactionType;
use App\Models\User;
use App\Models\AirtelTransaction;
use App\Models\HalotelTransaction;
// Add other MNO Transaction Models here if you expand (e.g., MpesaTransaction, TigoTransaction)
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Carbon\Carbon;

class TransactionController extends Controller
{
    public function sync(Request $request)
    {
        $authenticatedUser = $request->user(); // Wakala logged into mobile app
        $syncedCount = 0;
        $skippedCount = 0;

        // --- Device ID from mobile request ---
        $deviceIdFromMobile = $request->input('device_id'); // e.g., from DeviceInfo.getUniqueId()

        if (!$authenticatedUser) {
            return response()->json(['success' => false, 'message' => 'Uthibitishaji umeshindikana.'], 401);
        }
        if (!$deviceIdFromMobile) {
            return response()->json(['success' => false, 'message' => 'Kitambulisho cha kifaa kinahitajika.'], 400); // "Device ID is required."
        }

        // --- Find or Create the Device Record ---
        // Using the mobile's device_id as the primary key for your devices table is acceptable
        // IF your devices.id column is designed to store this string (e.g., VARCHAR or CHAR, not auto-increment INT)
        // AND if DeviceInfo.getUniqueId() is reliably unique and suitable as a primary key.
        // Assuming devices.id is a string (like UUID or the string from DeviceInfo.getUniqueId())
        $device = Device::firstOrCreate(
            ['id' => $deviceIdFromMobile], // Try to find by this ID
            ['name' => 'Kifaa Kipya - ' . $deviceIdFromMobile] // If creating, set a default name
        );
        // Note: If 'id' on your 'devices' table is auto-incrementing integer,
        // then you'd do Device::firstOrCreate(['unique_mobile_id' => $deviceIdFromMobile], ...)
        // and have a separate 'unique_mobile_id' column. But your current code suggests 'id' is the mobile's ID.

        // --- Determine Shop ID based on the device ---
        $shopId = null;
        if ($device->shop_id) {
            $shopId = $device->shop_id;
        } elseif ($authenticatedUser->assignedShops()->exists() && $authenticatedUser->assignedShops()->count() === 1) {
            // Fallback: If user is assigned to ONLY ONE shop, assume that shop.
            $shopId = $authenticatedUser->assignedShops()->first()->id;
        } else {
            Log::warning("Transaction Sync: Could not determine shop for device_id '{$deviceIdFromMobile}' used by user_id '{$authenticatedUser->id}'. Transactions will have NULL shop_id.");
            // No shop_id found. Transactions will be linked to the user but not a specific shop via device.
            // Admin will need to assign this device ($device) to a shop in Filament Panel.
        }

        foreach ($request->transactions as $txnData) {
            $mno = strtolower(trim($txnData['mno'] ?? 'unknownMNO')); // Provide a default if MNO is missing
            $refNo = $txnData['ref_no'];

            if (empty($refNo)) {
                Log::warning("Transaction Sync: Skipping transaction due to empty reference number.", $txnData);
                $skippedCount++;
                continue;
            }

            // Determine target model and check if transaction already exists in the correct table
            $targetModelInstance = null;
            $transactionModelClass = null;

            switch ($mno) {
                case 'airtel':
                    $transactionModelClass = AirtelTransaction::class;
                    break;
                case 'halotel':
                    $transactionModelClass = HalotelTransaction::class;
                    break;
                // case 'mpesa':
                //     $transactionModelClass = MpesaTransaction::class; // Example for future
                //     break;
                // case 'tigo':
                //     $transactionModelClass = TigoTransaction::class; // Example for future
                //     break;
                default:
                    Log::warning("Transaction Sync: Unsupported MNO '{$mno}' for ref '{$refNo}'. Skipping.");
                    $skippedCount++;
                    continue 2; // Continue to next iteration of foreach ($request->transactions...)
            }

            if ($transactionModelClass::where('ref_no', $refNo)->exists()) {
                $skippedCount++;
                continue;
            }
            $targetModelInstance = new $transactionModelClass();


            // --- Get or create related records (Customer, Type, OriginalSms) ---
            // These are fine as they were, using firstOrCreate for Customer and Type
            $customer = Customer::firstOrCreate(
                ['phone_number' => $txnData['customer_no']],
                ['name' => $txnData['customer_name']]
            );
            $typeName = $this->normalizeType($txnData['type']);
            $type = TransactionType::firstOrCreate(['name' => $typeName]);

            // Handling OriginalSms (ensure raw_sms column exists and is text/longtext)
            $rawSmsContent = is_string($txnData['raw']) ? $txnData['raw'] : json_encode($txnData['raw']);
            if (empty(trim($rawSmsContent))) { // Prevent saving empty SMS which might cause DB error if not nullable
                $sms = null; // Or handle differently, e.g. assign a default placeholder ID if sms_id is NOT NULL
            } else {
                $sms = OriginalSms::create(['raw_sms' => $rawSmsContent]);
            }


            // --- Construct Payload ---
            $payload = [
                // device_id: The ID of the 'Device' record in your database (which matches $deviceIdFromMobile)
                'device_id' => $device->id,
                'customer_id' => $customer->id,
                'sms_id' => $sms ? $sms->id : null, // Handle if SMS was not created
                'type_id' => $type->id,
                'user_id' => $authenticatedUser->id, // ID of the logged-in Wakala
                'shop_id' => $shopId, // ID of the shop associated with the device/user
                'ref_no' => $refNo,
                'date' => Carbon::parse($txnData['date'])->toDateTimeString(), // Ensure correct datetime format
                'amount' => (float) ($txnData['amount'] ?? 0),
                'commission' => (float) ($txnData['commission'] ?? 0),
                'float_balance' => (float) ($txnData['float_balance'] ?? $txnData['float'] ?? 0),
                'raw_payload' => json_encode($txnData), // Storing the whole transaction data from mobile
                'processed_at' => isset($txnData['createdAt']) ? Carbon::parse($txnData['createdAt'])->toDateTimeString() : now(),
                // Ensure all fields in $payload are in the $fillable array of AirtelTransaction, HalotelTransaction models.
            ];

            try {
                $newTransaction = $targetModelInstance->create($payload);
                // broadcast(new TransactionSynced($newTransaction));
                $syncedCount++;
            } catch (\Exception $e) {
                Log::error("Transaction Sync: Failed to create transaction for ref '{$refNo}'. Error: " . $e->getMessage(), ['payload' => $payload]);
                $skippedCount++;
            }
        }

        $message = "Miamala {$syncedCount} imesawazishwa kikamilifu.";
        if ($skippedCount > 0) {
            $message .= " Miamala {$skippedCount} ilirukwa (huenda tayari ipo au MNO haitumiki).";
        }

        return response()->json([
            'success' => true,
            'message' => $message,
            'user_id' => $authenticatedUser->id
        ]);
    }

    private function normalizeType($type)
    {
        $type = strtolower(trim((string)$type));
        if (in_array($type, ['weka', 'deposit', 'kuweka', 'deposits'])) return 'deposit'; // Added 'deposits'
        if (in_array($type, ['toa', 'withdrawal', 'kutoa', 'withdrawals'])) return 'withdrawal'; // Added 'withdrawals'
        return $type; // Return original if not matched, or a default like 'unknown'
    }
}
