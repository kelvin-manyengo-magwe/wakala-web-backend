<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('shops', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('name'); // Name of the shop/agent point
            $table->string('location')->nullable();

            // Foreign key to link to the user who primarily manages/owns this shop
            // Can be nullable if shops are centrally managed without a specific user owner at this level
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();

            // Initial cash given to this shop to start operations
            $table->decimal('initial_cash_on_hand', 15, 2)->default(0);

            // Storing initial MNO float allocations as JSON
            // Example: [{"mno_key": "airtel", "initial_float": 500000}, {"mno_key": "halotel", "initial_float": 300000}]
            $table->json('mno_initial_allocations')->nullable();

            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('shops');
    }
};
