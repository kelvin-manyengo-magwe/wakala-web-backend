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
        Schema::table('halotel_transactions', function (Blueprint $table) {
              $table->foreignUuid('shop_id')->nullable()->after('user_id')->constrained('shops')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('halotel_transactions', function (Blueprint $table) {
            //
        });
    }
};
