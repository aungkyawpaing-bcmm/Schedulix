<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('assignments', function (Blueprint $table) {
            $table->string('status', 30)->default('draft')->after('priority')->index();
            $table->boolean('is_critical')->default(false)->after('status')->index();
        });

        Schema::table('notifications', function (Blueprint $table) {
            $table->foreignId('assignment_id')->nullable()->after('project_id')->constrained('assignments')->nullOnDelete();
            $table->string('status', 30)->default('pending')->after('type')->index();
            $table->timestamp('sent_at')->nullable()->after('scheduled_for');
        });
    }

    public function down(): void
    {
        Schema::table('notifications', function (Blueprint $table) {
            $table->dropConstrainedForeignId('assignment_id');
            $table->dropColumn(['status', 'sent_at']);
        });

        Schema::table('assignments', function (Blueprint $table) {
            $table->dropColumn(['status', 'is_critical']);
        });
    }
};
