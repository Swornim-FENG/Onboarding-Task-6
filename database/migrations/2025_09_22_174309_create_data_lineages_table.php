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
        Schema::create('data_lineages', function (Blueprint $table) {
            $table->id();
            $table->string('data_element');     // logical id or name (e.g., "order_1234" or "customer.email")
            $table->string('action')->nullable();       
            $table->string('source')->nullable();      
            $table->string('transformation')->nullable(); 
            $table->string('destination')->nullable();  
            $table->json('metadata')->nullable();       
            $table->timestamp('occurred_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('data_lineages');
    }
};
