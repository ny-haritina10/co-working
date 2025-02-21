<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::create('client', function (Blueprint $table) {
            $table->id();
            $table->string('name_client');
            $table->string('numero_client')->unique();
            $table->timestamps();
        });

        Schema::create('options', function (Blueprint $table) {
            $table->id();
            $table->string('code');
            $table->string('label');
            $table->decimal('price', 10, 2);
            $table->timestamps();
        });

        Schema::create('espaces', function (Blueprint $table) {
            $table->id();
            $table->string('label')->unique();
            $table->decimal('hour_price', 10, 2);
            $table->timestamps();
        });

        Schema::create('reservations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('id_client')->constrained('client')->onDelete('cascade');
            $table->foreignId('id_espace')->constrained('espaces')->onDelete('cascade');
            $table->string('reference')->unique();
            $table->dateTime('datetime_reservation');
            $table->integer('hour_duration');
            $table->timestamps();
        });

        Schema::create('reservation_options', function (Blueprint $table) {
            $table->id();
            $table->foreignId('id_reservation')->constrained('reservations')->onDelete('cascade');
            $table->foreignId('id_option')->constrained('options')->onDelete('cascade');
            $table->timestamps();
        });

        Schema::create('paiements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('id_reservation')->constrained('reservations')->onDelete('cascade');
            $table->string('reference');
            $table->date('date_paiement');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('paiements');
        Schema::dropIfExists('reservation_options');
        Schema::dropIfExists('reservations');
        Schema::dropIfExists('espaces');
        Schema::dropIfExists('options');
        Schema::dropIfExists('client');
    }
};