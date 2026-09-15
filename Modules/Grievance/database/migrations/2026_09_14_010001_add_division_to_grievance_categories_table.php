<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('grievance_categories', function (Blueprint $table) {
            // Default routing required by ToR §4.1(c); staff may override it per case.
            $table->foreignId('division_id')->nullable()->constrained()->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('grievance_categories', function (Blueprint $table) {
            $table->dropConstrainedForeignId('division_id');
        });
    }
};
