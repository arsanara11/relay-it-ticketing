<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ticket_diagnoses', function (Blueprint $table) {
            $table->id();

            $table->foreignId('ticket_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->foreignId('technician_id')
                ->constrained('users')
                ->restrictOnDelete();

            $table->text('findings');
            $table->text('recommended_action')->nullable();

            $table->decimal('estimated_cost', 12, 2)
                ->nullable();

            $table->string('approval_status', 20)
                ->default('pending');

            $table->timestamp('approved_at')->nullable();
            $table->timestamp('diagnosed_at')->nullable();

            $table->timestamps();

            $table->index(['ticket_id', 'approval_status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ticket_diagnoses');
    }
};
