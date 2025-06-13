<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('business_investments', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->decimal('initial_investment_amount', 15, 2); // Amount with 2 decimal places
            $table->date('investment_date');
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('business_investments');
    }
};
