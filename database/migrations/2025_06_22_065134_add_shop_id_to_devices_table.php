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
        Schema::table('devices', function (Blueprint $table) {
             $table->foreignUuid('shop_id')->nullable()->after('name')->constrained('shops')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('devices', function (Blueprint $table) {
                  if (DB::getDriverName() !== 'sqlite') { // SQLite doesn't support dropForeign easily
                  $table->dropForeign(['shop_id']);
              }
              $table->dropColumn('shop_id');
        });
    }
};
