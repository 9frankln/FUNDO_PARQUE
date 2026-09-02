<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('scheduled_session_tasks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('fundo_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('tipo'); // reset | purge
            $table->dateTime('execute_at');
            $table->string('status')->default('pending'); // pending | done | cancelled | failed
            $table->text('result')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['status', 'execute_at']);
            $table->index(['user_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('scheduled_session_tasks');
    }
};
