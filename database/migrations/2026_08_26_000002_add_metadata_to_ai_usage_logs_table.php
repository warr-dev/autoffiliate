<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('ai_usage_logs', function (Blueprint $table) {
            if (! Schema::hasColumn('ai_usage_logs', 'execution_time_ms')) {
                $table->integer('execution_time_ms')->nullable()->after('estimated_cost');
            }
            if (! Schema::hasColumn('ai_usage_logs', 'source')) {
                $table->string('source')->default('manual')->after('execution_time_ms');
            }
            if (! Schema::hasColumn('ai_usage_logs', 'status')) {
                $table->string('status')->default('success')->after('source');
            }
        });
    }

    public function down(): void
    {
        Schema::table('ai_usage_logs', function (Blueprint $table) {
            $columns = [];
            if (Schema::hasColumn('ai_usage_logs', 'execution_time_ms')) {
                $columns[] = 'execution_time_ms';
            }
            if (Schema::hasColumn('ai_usage_logs', 'source')) {
                $columns[] = 'source';
            }
            if (Schema::hasColumn('ai_usage_logs', 'status')) {
                $columns[] = 'status';
            }
            if (! empty($columns)) {
                $table->dropColumn($columns);
            }
        });
    }
};
