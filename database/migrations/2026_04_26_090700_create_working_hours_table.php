<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('working_hours', function (Blueprint $table) {
            $table->id();
            $table->string('scope_type', 20)->default('global');
            $table->foreignId('project_id')->nullable()->constrained()->cascadeOnDelete();
            $table->unsignedTinyInteger('weekday');
            $table->time('start_time')->nullable();
            $table->time('end_time')->nullable();
            $table->time('lunch_start_time')->nullable();
            $table->time('lunch_end_time')->nullable();
            $table->decimal('net_hours', 5, 2)->default(0);
            $table->boolean('is_working_day')->default(true);
            $table->timestamps();

            $table->unique(['scope_type', 'project_id', 'weekday'], 'working_hours_scope_weekday_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('working_hours');
    }
};
