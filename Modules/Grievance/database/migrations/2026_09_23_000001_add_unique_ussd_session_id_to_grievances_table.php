<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('grievances', function (Blueprint $table): void {
            $table->unique('ussd_session_id', 'grievances_ussd_session_id_unique');
        });
    }

    public function down(): void
    {
        Schema::table('grievances', function (Blueprint $table): void {
            $table->dropUnique('grievances_ussd_session_id_unique');
        });
    }
};
