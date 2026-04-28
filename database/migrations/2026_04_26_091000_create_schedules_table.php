<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('schedules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained()->cascadeOnDelete();
            $table->foreignId('assignment_id')->constrained('assignments')->cascadeOnDelete();
            $table->date('planned_start_date')->nullable();
            $table->date('planned_end_date')->nullable();
            $table->date('actual_start_date')->nullable();
            $table->date('actual_end_date')->nullable();
            $table->decimal('planned_hours', 8, 2)->default(0);
            $table->decimal('actual_total_hours', 8, 2)->default(0);
            $table->decimal('digestion_hours', 8, 2)->default(0);
            $table->decimal('remaining_hours', 8, 2)->default(0);
            $table->decimal('progress_percent', 5, 2)->default(0);
            $table->boolean('is_overdue')->default(false)->index();
            $table->text('warning_notes')->nullable();
            $table->timestamps();

            $table->unique('assignment_id');
            $table->index(['project_id', 'planned_start_date', 'planned_end_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('schedules');
    }
};
