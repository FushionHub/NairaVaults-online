<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('loans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->decimal('amount', 20, 8);
            $table->string('currency', 10);
            $table->integer('tenure_months');
            $table->text('purpose')->nullable();
            $table->decimal('interest_rate', 5, 4);
            $table->decimal('total_repayable', 20, 8);
            $table->string('status', 20)->default('pending');
            $table->timestamp('disbursed_at')->nullable();
            $table->json('repayment_schedule')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('loans');
    }
};
