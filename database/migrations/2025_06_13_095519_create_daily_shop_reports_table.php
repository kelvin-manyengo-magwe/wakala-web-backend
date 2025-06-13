<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('daily_shop_reports', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('shop_id')->constrained('shops')->cascadeOnDelete();
            $table->date('report_date'); // The day this report is for

            // Financials for the day at this shop
            $table->decimal('total_deposits_amount', 15, 2)->default(0);
            $table->decimal('total_withdrawals_amount', 15, 2)->default(0);
            $table->decimal('total_commission_earned', 15, 2)->default(0);

            // Balances
            $table->decimal('opening_cash_on_hand', 15, 2);
            $table->decimal('closing_cash_on_hand', 15, 2); // Can be manual entry or calculated

            // Storing MNO float balances for the day as JSON for flexibility
            // Example: {"airtel": {"opening": 100, "closing": 150}, "halotel": {"opening": 200, "closing": 180}}
            $table->json('mno_float_balances')->nullable();

            $table->decimal('calculated_daily_profit', 15, 2)->nullable(); // (Closing Cash - Opening Cash) + (Total Closing Floats - Total Opening Floats) + Commission

            // User who submitted/verified this report (if manual process)
            $table->foreignId('submitted_by_user_id')->nullable()->constrained('users')->nullOnDelete();

            $table->text('notes')->nullable(); // Any notes for the day
            $table->timestamps();

            $table->unique(['shop_id', 'report_date']); // Ensure only one report per shop per day
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('daily_shop_reports');
    }
};
