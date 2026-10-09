<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('grievances', function (Blueprint $table): void {
            $table->foreignId('grievance_category_id')
                ->nullable()
                ->change();
        });
    }

    public function down(): void
    {
        if (DB::table('grievances')->whereNull('grievance_category_id')->exists()) {
            $fallbackCategoryId = DB::table('grievance_categories')
                ->where('slug', 'other')
                ->value('id')
                ?? DB::table('grievance_categories')->value('id');

            if (! $fallbackCategoryId) {
                throw new RuntimeException('Cannot roll back category nullability while uncategorized grievances exist without any categories.');
            }

            DB::table('grievances')
                ->whereNull('grievance_category_id')
                ->update(['grievance_category_id' => $fallbackCategoryId]);
        }

        Schema::table('grievances', function (Blueprint $table): void {
            $table->foreignId('grievance_category_id')
                ->nullable(false)
                ->change();
        });
    }
};
