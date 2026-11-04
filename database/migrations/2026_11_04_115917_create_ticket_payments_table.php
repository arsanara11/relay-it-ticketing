<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ticket_payments', function (Blueprint $table) {
            $table->id();

            $table->foreignId('ticket_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->foreignId('recorded_by')
                ->constrained('users')
                ->restrictOnDelete();

            $table->decimal('amount', 12, 2);
            $table->string('payment_method', 30);

            $table->string('reference_number', 100)->nullable();
            $table->text('notes')->nullable();

            $table->timestamp('paid_at');
            $table->timestamps();

            $table->index(['ticket_id', 'paid_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ticket_payments');
    }
};
