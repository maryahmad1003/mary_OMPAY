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
        Schema::create('users', function (Blueprint $table) {
            // UUID primary key
            $table->uuid('id')->primary();

            $table->string('nom')->nullable();
            $table->string('prenom')->nullable();
            // maintain compatibility with existing 'name'
            $table->string('name')->nullable();

            // telephone is required and unique for this app
            $table->string('telephone')->unique();

            $table->string('email')->unique()->nullable();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');

            $table->string('status')->default('client');
            $table->string('cni')->nullable();
            $table->string('login_token')->nullable()->index();
            $table->boolean('is_verified')->default(false)->index();
            $table->string('qr_code')->nullable();

            $table->rememberToken();
            $table->timestamps();

            // indexes
            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};
