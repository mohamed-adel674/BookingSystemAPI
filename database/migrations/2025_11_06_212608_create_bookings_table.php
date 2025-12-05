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
    Schema::create('bookings', function (Blueprint $table) {
        $table->id();
        $table->foreignId('user_id')->constrained()->cascadeOnDelete();
        $table->foreignId('resource_id')->constrained()->cascadeOnDelete();

        $table->dateTime('start_time');
        $table->dateTime('end_time');

        $table->enum('status', ['pending', 'confirmed', 'cancelled', 'completed'])->default('pending');
        $table->timestamps();

        // Index لسرعة البحث وتطبيق القيود لاحقاً
        $table->index(['resource_id', 'start_time', 'end_time']); 
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bookings');
    }
};
