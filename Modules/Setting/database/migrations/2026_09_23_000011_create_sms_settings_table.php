<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sms_settings', function (Blueprint $table): void {
            $table->id();
            $table->string('name');
            $table->string('provider');
            $table->string('base_url')->nullable();
            $table->string('api_key')->nullable();
            $table->text('api_secret')->nullable();
            $table->string('sender_id')->nullable();
            $table->string('default_country_code', 10)->default('+266');
            $table->boolean('is_active')->default(false);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sms_settings');
    }
};
