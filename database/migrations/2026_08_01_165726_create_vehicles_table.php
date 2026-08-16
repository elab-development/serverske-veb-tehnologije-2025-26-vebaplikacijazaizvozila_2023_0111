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
        Schema::create('vehicles', function (Blueprint $table) {
        $table->id();

        $table->foreignId('category_id')
            ->constrained()
            ->restrictOnDelete();

        $table->string('brand');
        $table->string('model');
        $table->string('registration_number')->unique();
        $table->unsignedSmallInteger('production_year');
        $table->decimal('daily_price', 10, 2);
        $table->string('transmission');
        $table->string('fuel_type');
        $table->unsignedTinyInteger('seats');
        $table->string('status')->default('available');
        $table->text('description')->nullable();

        $table->index(['brand', 'model']);
        $table->timestamps();
    });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vehicles');
    }
};
