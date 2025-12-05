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
    Schema::create('availabilities', function (Blueprint $table) {
        $table->id();
        $table->foreignId('resource_id')->constrained()->cascadeOnDelete(); 

        // 1=الاثنين, 7=الأحد
        $table->unsignedTinyInteger('day_of_week')->comment('1=Mon, 7=Sun'); 
        
        $table->time('start_time');
        $table->time('end_time');

        // لتحديد فترات زمنية استثنائية (مثل إغلاق مؤقت)
        $table->date('date_from')->nullable(); 
        $table->date('date_to')->nullable();

        $table->timestamps();
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('availabilities');
    }
};
