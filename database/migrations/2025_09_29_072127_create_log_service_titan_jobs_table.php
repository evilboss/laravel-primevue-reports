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
        Schema::create('log_service_titan_jobs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('market_id')->constrained()->onDelete('cascade');
            $table->string('job_id')->nullable();
            $table->date('booking_date');
            $table->decimal('booking_amount', 10, 2)->nullable();
            $table->string('customer_type')->nullable();
            $table->string('service_type')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->index(['market_id', 'booking_date']);
            $table->index('booking_date');
            $table->index('market_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('log_service_titan_jobs');
    }
};
