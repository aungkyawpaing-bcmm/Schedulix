<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('assignments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained()->cascadeOnDelete();
            $table->foreignId('project_wbs_item_id')->constrained()->cascadeOnDelete();
            $table->foreignId('assigned_pic_id')->constrained('users')->restrictOnDelete();
            $table->foreignId('project_manager_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('assigned_role', 30)->default('member');
            $table->string('priority', 30)->default('medium')->index();
            $table->decimal('planned_hours', 8, 2);
            $table->decimal('plan_rest_hours', 8, 2)->nullable();
            $table->boolean('auto_create_schedule')->default(true);
            $table->text('remark')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->unique('project_wbs_item_id');
            $table->index(['project_id', 'assigned_pic_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('assignments');
    }
};
