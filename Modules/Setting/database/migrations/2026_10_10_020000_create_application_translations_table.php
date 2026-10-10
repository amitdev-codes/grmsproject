<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('application_translations', function (Blueprint $table) {
            $table->id();
            $table->string('translation_key', 191)->unique();
            $table->text('english_text');
            $table->text('sesotho_text')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('application_translations');
    }
};
