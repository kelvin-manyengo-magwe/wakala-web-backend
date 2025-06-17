<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('shop_user', function (Blueprint $table) {
            $table->id(); // Auto-incrementing ID for the pivot record
            $table->foreignUuid('shop_id')->constrained('shops')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            // Add other pivot data here if ever needed (e.g., date_assigned)
            // $table->timestamp('assigned_at')->useCurrent();
            $table->timestamps(); // Optional, useful for tracking when assignment changed

            $table->unique(['shop_id', 'user_id']); // Each user can only be assigned to a shop once
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('shop_user');
    }
};
