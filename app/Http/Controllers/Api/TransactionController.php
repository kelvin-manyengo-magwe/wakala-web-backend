<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Customer;
use App\Models\Device;
use App\Models\OriginalSms;
use App\Models\TransactionType;
use App\Models\User;
use App\Models\Shop;
use App\Models\AirtelTransaction;
use App\Models\HalotelTransaction;
// If you add other MNOs like Mpesa, Tigo, import their models here:
// use App\Models\MpesaTransaction;
// use App\Models\TigoTransaction;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Carbon\Carbon;

class TransactionController extends Controller
{
    /**
     * Synchronize transactions from the mobile application.
     */
    public function sync(Request $request)
    {
        $authenticatedUser = $request->user(); // The Wakala logged in on the mobile app
        $deviceIdFromMobile = $request->input('device_id'); // Unique ID from DeviceInfo.getUniqueId()

        $syncedCount = 0;
        $skippedCount = 0;

        // --- Basic Validations ---
        if (!$authenticatedUser) {
            Log::warning('Transaction Sync: Unauthenticated API access attempt.');
            return response()->json(['success' => false, 'message' => 'Uthibitishaji umeshindikana. Tafadhali ingia tena.'], 401);
        }
        if (empty($deviceIdFromMobile)) {
            Log::warning("Transaction Sync: Missing device_id from user_id: {$authenticatedUser->id}.");
            return response()->json(['success' => false, 'message' => 'Kitambulisho cha kifaa kinahitajika.'], 400);
        }

        // --- Device Handling & Shop ID Determination ---
        $device = Device::find($deviceIdFromMobile);
        if (!$device) {
            // Device ID sent from mobile does not exist in our devices table yet. Create it.
            // The admin will need to assign this new device to a Shop in the Filament panel.
            $deviceName = 'Kifaa Kipya - ' . Str::limit($deviceIdFromMobile, 25) . ' (cha Mtumiaji: '.$authenticatedUser->name.')';
            $device = Device::create([
                'id' => $deviceIdFromMobile, // Assuming devices.id can store this string value
                'name' => $deviceName
                // 'shop_id' will be null by default for new devices
            ]);
            Log::info("Transaction Sync: Kifaa kipya kimesajiliwa. Device ID '{$deviceIdFromMobile}' na Mtumiaji '{$authenticatedUser->name}'. Msimamizi anahitaji kukihusisha na Duka.");
        }

        $shopId = null;
        if ($device->shop_id) {
            // Primary way: The device is registered and assigned to a shop.
            $shopId = $device->shop_id;
            Log::info("Transaction Sync: Shop ID '{$shopId}' found via Device '{$deviceIdFromMobile}'.");
        } elseif ($authenticatedUser->assignedShops()->count() === 1) {
            // Fallback: If device isn't assigned, but the Wakala (User) is assigned to ONLY ONE shop.
            $userOnlyShop = $authenticatedUser->assignedShops()->first(); // Requires assignedShops() relationship on User model
            if($userOnlyShop){
                 $shopId = $userOnlyShop->id;
                 Log::info("Transaction Sync: Shop ID '{$shopId}' ('{$userOnlyShop->name}') found from User '{$authenticatedUser->name}'s single shop assignment for Device '{$deviceIdFromMobile}'.");
            }
        }

        if (is_null($shopId)) {
            Log::warning("Transaction Sync: Imeshindikana kutambua Duka kwa muamala unaotoka kwa Mtumiaji ID: {$authenticatedUser->id}, Kifaa ID: {$deviceIdFromMobile}. Miamala itawekwa na shop_id=NULL.");
        }

        // --- Process Each Transaction ---
        foreach ($request->transactions as $txnData) {
            $mnoIdentifier = strtolower(trim($txnData['mno'] ?? 'haijulikani')); // 'airtel', 'halotel', etc.
            $referenceNumber = trim($txnData['ref_no'] ?? '');

            if (empty($referenceNumber)) {
                Log::warning("Transaction Sync: Transaction from user_id: {$authenticatedUser->id}, MNO: {$mnoIdentifier} is missing ref_no. Skipping.", ['transaction_data' => $txnData]);
                $skippedCount++;
                continue;
            }

            $targetModelClass = null; // e.g., AirtelTransaction::class
            switch ($mnoIdentifier) {
                case 'airtel':  $targetModelClass = AirtelTransaction::class; break;
                case 'halotel': $targetModelClass = HalotelTransaction::class; break;
                // Add other MNOs here:
                // case 'mpesa':   $targetModelClass = MpesaTransaction::class; break;
                // case 'tigo':    $targetModelClass = TigoTransaction::class; break;
                default:
                    Log::warning("Transaction Sync: MNO '{$mnoIdentifier}' haitumiki kwa ref '{$referenceNumber}' from user_id: {$authenticatedUser->id}. Inarukwa.");
                    $skippedCount++;
                    continue 2; // Continues to the next transaction in the $request->transactions loop
            }

            // Check for duplicate transaction using ONLY ref_no within the specific MNO's table
            if ($targetModelClass::where('ref_no', $referenceNumber)->exists()) {
                // Log::info("Transaction Sync: Duplicate ref_no '{$referenceNumber}' for MNO '{$mnoIdentifier}' (table '{$targetModelClass}') from user_id: {$authenticatedUser->id}. Skipping.");
                $skippedCount++;
                continue;
            }

            $targetModelInstance = new $targetModelClass();

            // Related records
            $customerName = trim($txnData['customer_name'] ?? 'Mteja (Hakuna Jina)');
            $customerNumber = trim($txnData['customer_no'] ?? '');
            $customerNumber = !empty($customerNumber) ? $customerNumber : '000000000'; // Ensure a value

            $customer = Customer::firstOrCreate(
                ['phone_number' => $customerNumber],
                ['name' => $customerName]
            );

            $transactionTypeName = $this->normalizeType($txnData['type']);
            $type = TransactionType::firstOrCreate(['name' => $transactionTypeName]);

            $rawSmsContent = is_string($txnData['raw']) ? trim($txnData['raw']) : json_encode($txnData['raw']);
            $smsRecord = null;
            if (!empty($rawSmsContent) && $rawSmsContent !== 'null' && $rawSmsContent !== '""') {
                $smsRecord = OriginalSms::create(['raw_sms' => $rawSmsContent]);
            }

            // Prepare payload for creating the transaction
            $payload = [
                'device_id'     => $device->id, // From device found/created earlier
                'customer_id'   => $customer->id,
                'sms_id'        => $smsRecord?->id,
                'type_id'       => $type->id,
                'user_id'       => $authenticatedUser->id, // The Wakala
                'shop_id'       => $shopId,               // The Shop
                'ref_no'        => $referenceNumber,
                'date'          => Carbon::parse($txnData['date'])->toDateTimeString(),
                'amount'        => (float)($txnData['amount'] ?? 0.00),
                'commission'    => (float)($txnData['commission'] ?? 0.00),
                'float_balance' => (float)($txnData['float_balance'] ?? $txnData['float'] ?? 0.00),
                'raw_payload'   => json_encode($txnData), // Original data from mobile for this transaction
                'processed_at'  => isset($txnData['createdAt']) && !empty($txnData['createdAt'])
                                     ? Carbon::parse($txnData['createdAt'])->toDateTimeString()
                                     : Carbon::parse($txnData['date'])->toDateTimeString(),
                // DO NOT include 'mno' here if individual tables (AirtelTransaction, etc.) don't have it.
            ];

            try {
                $targetModelInstance->fill($payload)->save(); // Creates the transaction
                $syncedCount++;
            } catch (\Exception $e) {
                Log::error("Transaction Sync: FAILED TO SAVE transaction. Ref: '{$referenceNumber}', MNO: '{$mnoIdentifier}', User: {$authenticatedUser->id}. Error: " . $e->getMessage(), ['payload_keys' => array_keys($payload), 'exception_trace' => $e->getTraceAsString()]);
                $skippedCount++;
            }
        }

        $message = "{$syncedCount} miamala imesawazishwa kikamilifu.";
        if ($skippedCount > 0) {
            $message .= " {$skippedCount} miamala ilirukwa (huenda tayari ipo au MNO haitumiki).";
        }

        return response()->json(['success' => true, 'message' => $message, 'user_id' => $authenticatedUser->id]);
    }

    private function normalizeType($typeFromMobile): string
    {
        $type = strtolower(trim((string)$typeFromMobile));
        if (in_array($type, ['weka', 'deposit', 'deposits', 'kuweka'])) {
            return 'deposit';
        }
        if (in_array($type, ['toa', 'withdrawal', 'withdrawals', 'kutoa'])) {
            return 'withdrawal';
        }
        Log::info("Transaction Sync: Unrecognized transaction type: '{$typeFromMobile}' normalized to '{$type}'. Consider adding to normalizeType function.");
        return !empty($type) ? $type : 'unknown'; // Return cleaned type or default
    }
}
