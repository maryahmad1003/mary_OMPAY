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
        Schema::create('transactions', function (Blueprint $table) {
            $table->uuid('id')->primary();

            $table->uuid('compte_source_id');
            $table->uuid('compte_destination_id')->nullable();

            $table->string('type'); // depot, retrait, transfert, scan
            $table->decimal('montant', 15, 2);
            $table->string('status')->default('pending'); // pending, success, failed, cancelled
            $table->string('reference')->unique();
            $table->text('description')->nullable();
            $table->string('mode')->nullable(); // ussd, qr, mobile_app, api

            $table->timestamps();

            $table->foreign('compte_source_id')->references('id')->on('comptes')->onDelete('cascade');
            $table->foreign('compte_destination_id')->references('id')->on('comptes')->onDelete('set null');

            $table->index('compte_source_id');
            $table->index('compte_destination_id');
            $table->index('type');
            $table->index('status');
            $table->index('mode');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transactions');
    }
};
