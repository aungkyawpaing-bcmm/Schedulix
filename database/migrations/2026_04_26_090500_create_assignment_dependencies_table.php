<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('assignment_dependencies', function (Blueprint $table) {
            $table->id();
            $table->foreignId('assignment_id')->constrained('assignments')->cascadeOnDelete();
            $table->foreignId('depends_on_assignment_id')->constrained('assignments')->cascadeOnDelete();
            $table->string('dependency_type', 10)->default('FS');
            $table->timestamps();

            $table->unique(['assignment_id', 'depends_on_assignment_id'], 'assignment_dependency_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('assignment_dependencies');
    }
};
