<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tickets', function (Blueprint $table) {
            $table->id();

            $table->string('ticket_number', 30)->unique();

            $table->foreignId('customer_id')
                ->constrained()
                ->restrictOnDelete();

            $table->foreignId('device_id')
                ->constrained()
                ->restrictOnDelete();

            $table->foreignId('ticket_category_id')
                ->constrained()
                ->restrictOnDelete();

            $table->foreignId('technician_id')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->foreignId('created_by')
                ->constrained('users')
                ->restrictOnDelete();

            $table->string('subject', 200);
            $table->text('description');

            $table->string('priority', 20)->default('medium');
            $table->string('status', 30)->default('received');

            $table->text('initial_condition')->nullable();
            $table->text('accessories')->nullable();

            $table->timestamp('received_at')->nullable();
            $table->timestamp('resolved_at')->nullable();
            $table->timestamp('closed_at')->nullable();

            $table->timestamps();

            $table->index(['status', 'priority']);
            $table->index('technician_id');
            $table->index('received_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tickets');
    }
};
