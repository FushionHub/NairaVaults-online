<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('support_tickets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('subject');
            $table->string('category', 50);
            $table->string('priority', 20)->default('medium');
            $table->string('status', 20)->default('open');
            $table->timestamp('closed_at')->nullable();
            $table->timestamps();
        });

        Schema::create('support_messages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ticket_id')->constrained('support_tickets')->cascadeOnDelete();
            $table->unsignedBigInteger('sender_id');
            $table->boolean('is_admin')->default(false);
            $table->text('message');
            $table->json('attachments')->nullable();
            $table->timestamps();

            $table->foreign('sender_id')->references('id')->on('users');
        });

        Schema::create('push_notification_tokens', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('token');
            $table->string('platform', 20);
            $table->json('device_info')->nullable();
            $table->timestamps();

            $table->unique(['user_id', 'token']);
        });

        Schema::create('receipts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('transaction_id')->constrained()->cascadeOnDelete();
            $table->string('receipt_number')->unique();
            $table->string('file_path')->nullable();
            $table->timestamps();
        });

        Schema::create('statements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('type', 20);
            $table->date('start_date');
            $table->date('end_date');
            $table->string('file_path')->nullable();
            $table->string('format', 10)->default('pdf');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('statements');
        Schema::dropIfExists('receipts');
        Schema::dropIfExists('push_notification_tokens');
        Schema::dropIfExists('support_messages');
        Schema::dropIfExists('support_tickets');
    }
};
