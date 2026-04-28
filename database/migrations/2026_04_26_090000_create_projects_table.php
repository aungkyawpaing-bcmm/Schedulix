<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('projects', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('code', 50)->unique();
            $table->foreignId('project_manager_id')->constrained('users')->restrictOnDelete();
            $table->date('expected_start_date');
            $table->date('expected_end_date');
            $table->text('overview')->nullable();
            $table->text('objective')->nullable();
            $table->unsignedInteger('team_size')->nullable();
            $table->string('timezone')->default('Asia/Yangon');
            $table->string('status', 30)->default('draft')->index();
            $table->string('locale_default', 10)->default('en');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('projects');
    }
};
