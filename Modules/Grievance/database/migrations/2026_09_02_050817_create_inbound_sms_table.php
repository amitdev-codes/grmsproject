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
        Schema::create('inbound_sms', function (Blueprint $table) {
            $table->id();
            $table->string('from_number', 20);
            $table->text('raw_message');
            $table->string('gateway_message_id')->nullable();
            $table->string('status', 20)->default('pending'); // pending, registered, spam, duplicate
            $table->foreignId('grievance_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('registered_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('inbound_sms');
    }
};
