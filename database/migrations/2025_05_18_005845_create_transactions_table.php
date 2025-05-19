<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('transactions', function (Blueprint $table) {
          $table->uuid('id')->primary();
          $table->uuid('device_id');
          $table->uuid('customer_id');
          $table->uuid('sms_id');
          $table->uuid('type_id');
          $table->string('ref_no');
          $table->dateTime('date');
          $table->decimal('amount', 12, 2);
          $table->decimal('commission', 12, 2);
          $table->decimal('float', 12, 2);
          $table->text('raw');
          $table->timestamp('createdAt');
          $table->timestamps();

          $table->foreign('device_id')->references('id')->on('devices')->onDelete('cascade');
          $table->foreign('sms_id')->references('id')->on('original_sms')->onDelete('cascade');
          $table->foreign('customer_id')->references('id')->on('customers')->onDelete('cascade');
          $table->foreign('type_id')->references('id')->on('transaction_types')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transactions');
    }
};
