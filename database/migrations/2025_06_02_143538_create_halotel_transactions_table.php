<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('halotel_transactions', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('device_id')->constrained('devices')->cascadeOnDelete();
            $table->foreignUuid('customer_id')->constrained('customers')->cascadeOnDelete();
            $table->foreignUuid('sms_id')->constrained('original_sms')->cascadeOnDelete();
            $table->foreignUuid('type_id')->constrained('transaction_types')->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete(); // Wakala ID

            $table->string('ref_no')->unique();
            $table->dateTime('date');
            $table->decimal('amount', 12, 2);
            $table->decimal('commission', 12, 2)->nullable();
            $table->decimal('float_balance', 12, 2)->nullable(); // New name for 'float'
            $table->text('raw_payload'); // Renamed from 'raw'
            $table->timestamp('processed_at'); // From mobile app's 'createdAt'
            $table->timestamps(); // Laravel's created_at and updated_at
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('halotel_transactions');
    }
};
