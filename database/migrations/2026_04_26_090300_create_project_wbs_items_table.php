<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('project_wbs_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained()->cascadeOnDelete();
            $table->foreignId('parent_id')->nullable()->constrained('project_wbs_items')->nullOnDelete();
            $table->foreignId('task_master_id')->nullable()->constrained('task_master')->nullOnDelete();
            $table->string('wbs_number', 50);
            $table->unsignedTinyInteger('level');
            $table->string('item_name');
            $table->string('item_type', 50)->index();
            $table->string('content_item_type', 50)->nullable()->index();
            $table->string('platform', 50)->nullable()->index();
            $table->text('description')->nullable();
            $table->boolean('is_assignable')->default(false);
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['project_id', 'wbs_number']);
            $table->index(['project_id', 'parent_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('project_wbs_items');
    }
};
