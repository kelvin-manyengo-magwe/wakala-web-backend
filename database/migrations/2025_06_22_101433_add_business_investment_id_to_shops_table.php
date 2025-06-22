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
        Schema::table('shops', function (Blueprint $table) {
              $table->foreignUuid('business_investment_id') // Assuming business_investments uses UUIDs
                   ->nullable()
                   ->after('user_id')
                   ->constrained('business_investments') // Assumes table name is 'business_investments'
                   ->nullOnDelete();
            });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
            Schema::table('shops', function (Blueprint $table) {
                  if (DB::getDriverName() !== 'sqlite') { // SQLite doesn't support dropForeign easily
                      $table->dropForeign(['business_investment_id']);
                  }
                  $table->dropColumn('business_investment_id');
              });
    }
};
