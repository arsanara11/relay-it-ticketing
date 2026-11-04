<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ticket_handovers', function (Blueprint $table) {
            $table->id();

            $table->foreignId('ticket_id')
                ->unique()
                ->constrained()
                ->cascadeOnDelete();

            $table->foreignId('handed_over_by')
                ->constrained('users')
                ->restrictOnDelete();

            $table->string('received_by', 150);
            $table->text('notes')->nullable();

            $table->timestamp('handed_over_at');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ticket_handovers');
    }
};
