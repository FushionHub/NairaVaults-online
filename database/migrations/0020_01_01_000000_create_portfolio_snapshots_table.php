<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('portfolio_snapshots', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->decimal('total_value', 20, 8);
            $table->decimal('fiat_value', 20, 8);
            $table->decimal('crypto_value', 20, 8);
            $table->date('snapshot_date');
            $table->json('breakdown')->nullable();
            $table->timestamps();

            $table->unique(['user_id', 'snapshot_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('portfolio_snapshots');
    }
};
