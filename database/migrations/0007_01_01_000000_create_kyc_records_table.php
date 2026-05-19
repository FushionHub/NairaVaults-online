<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('kyc_records', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained()->cascadeOnDelete();
            $table->text('bvn_encrypted')->nullable();
            $table->text('nin_encrypted')->nullable();
            $table->string('id_type', 30)->nullable();
            $table->text('id_number_encrypted')->nullable();
            $table->string('selfie_url')->nullable();
            $table->string('id_document_url')->nullable();
            $table->string('dojah_verification_id')->nullable();
            $table->string('status', 20)->default('pending');
            $table->text('rejection_reason')->nullable();
            $table->string('business_name')->nullable();
            $table->string('business_type')->nullable();
            $table->string('rc_number')->nullable();
            $table->string('cac_document_url')->nullable();
            $table->string('tax_id_encrypted')->nullable();
            $table->timestamp('submitted_at')->nullable();
            $table->timestamp('verified_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('kyc_records');
    }
};
