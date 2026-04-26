<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('bugs', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('description');

            $table->enum('status', [
                'open',
                'in_progress',
                'resolved',
                'closed'
            ])->default('open');

            $table->enum('severity', [
                'low',
                'medium',
                'high',
                'critical'
            ])->default('medium');

            $table->foreignId('created_by')
                  ->constrained('users')
                  ->cascadeOnDelete();

            $table->foreignId('category_id')
                  ->constrained()
                  ->cascadeOnDelete();

            $table->foreignId('assigned_admin')
                  ->nullable()
                  ->constrained('users')
                  ->nullOnDelete();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bugs');
    }
};
