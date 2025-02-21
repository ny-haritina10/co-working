<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::create('properties', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->float('surface');
            $table->decimal('price', 10, 2);
            $table->text('description');
            $table->integer('rooms');
            $table->integer('bedrooms');
            $table->integer('floors');
            $table->string('address');
            $table->string('city');
            $table->string('postal_code');
            $table->foreignId('specificity_id')->constrained();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('properties');
    }
};
