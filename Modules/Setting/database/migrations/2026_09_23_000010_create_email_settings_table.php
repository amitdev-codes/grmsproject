<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('email_settings', function (Blueprint $table): void {
            $table->id();
            $table->string('name');
            $table->string('mailer')->default('smtp');
            $table->string('host');
            $table->unsignedSmallInteger('port')->default(2525);
            $table->string('encryption')->nullable();
            $table->string('username')->nullable();
            $table->text('password')->nullable();
            $table->string('from_address');
            $table->string('from_name');
            $table->boolean('is_active')->default(false);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('email_settings');
    }
};
