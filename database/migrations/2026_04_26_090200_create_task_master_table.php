<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('task_master', function (Blueprint $table) {
            $table->id();
            $table->string('task_code', 50)->unique();
            $table->string('name');
            $table->string('content_item_type', 50)->index();
            $table->string('platform', 50)->index();
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true)->index();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('task_master');
    }
};
