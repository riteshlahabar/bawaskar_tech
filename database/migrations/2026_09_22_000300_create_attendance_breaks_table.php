<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('attendance_breaks', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('attendance_log_id')->constrained('attendance_logs')->cascadeOnDelete();
            $table->timestamp('break_at');
            $table->timestamp('resume_at')->nullable();
            $table->decimal('break_latitude', 10, 7)->nullable();
            $table->decimal('break_longitude', 10, 7)->nullable();
            $table->decimal('resume_latitude', 10, 7)->nullable();
            $table->decimal('resume_longitude', 10, 7)->nullable();
            $table->unsignedInteger('break_minutes')->default(0);
            $table->timestamps();
            $table->index(['attendance_log_id', 'break_at']);
        });

        Schema::table('attendance_logs', function (Blueprint $table): void {
            // Total of the day's finished breaks, kept on the parent row so the
            // admin list and the payroll/report queries never have to aggregate
            // the child table just to show a number.
            $table->unsignedInteger('break_minutes')->default(0)->after('working_minutes');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('attendance_breaks');

        Schema::table('attendance_logs', function (Blueprint $table): void {
            $table->dropColumn('break_minutes');
        });
    }
};
