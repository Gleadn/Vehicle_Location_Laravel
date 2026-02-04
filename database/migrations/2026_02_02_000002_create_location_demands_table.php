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
        Schema::create('location_demands', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->enum('vehicle_type', ['car', 'motorcycle', 'van', 'sport']);
            $table->date('start_date');
            $table->date('end_date');
            $table->integer('seats_required')->nullable();
            $table->decimal('max_budget', 8, 2)->nullable();
            $table->enum('status', ['pending', 'processing', 'proposed', 'accepted', 'rejected', 'expired'])->default('pending');
            $table->text('notes')->nullable();
            $table->timestamp('processed_at')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('location_demands');
    }
};
