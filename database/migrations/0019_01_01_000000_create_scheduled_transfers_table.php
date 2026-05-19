<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('scheduled_transfers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->unsignedBigInteger('from_account_id');
            $table->unsignedBigInteger('to_account_id')->nullable();
            $table->string('to_external_account')->nullable();
            $table->string('to_bank_code')->nullable();
            $table->decimal('amount', 20, 8);
            $table->string('currency', 10);
            $table->string('frequency', 20);
            $table->date('next_run_date');
            $table->date('end_date')->nullable();
            $table->string('status', 20)->default('active');
            $table->timestamps();

            $table->foreign('from_account_id')->references('id')->on('fiat_accounts');
            $table->foreign('to_account_id')->references('id')->on('fiat_accounts')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('scheduled_transfers');
    }
};
