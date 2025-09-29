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
        Schema::create('log_events', function (Blueprint $table) {
            $table->id();
            $table->foreignId('market_id')->constrained()->onDelete('cascade');
            $table->string('session_id');
            $table->enum('event_type', [
                'job_type_zip_completed',
                'appointment_datetime_selected', 
                'customer_selection',
                'terms_of_service_loaded',
                'appointment_confirmed'
            ]);
            $table->timestamp('event_timestamp');
            $table->json('event_data')->nullable();
            $table->timestamps();

            $table->index(['market_id', 'event_timestamp']);
            $table->index(['session_id', 'event_type']);
            $table->index('session_id');
            $table->index('market_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('log_events');
    }
};
