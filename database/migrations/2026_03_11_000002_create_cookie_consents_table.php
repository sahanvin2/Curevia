<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cookie_consents', function (Blueprint $table) {
            $table->id();
            $table->string('ip_hash', 64);
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('consent_type', 20); // all, essential, reject
            $table->string('user_agent_hash', 64)->nullable();
            $table->timestamps();

            $table->index('ip_hash');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cookie_consents');
    }
};
