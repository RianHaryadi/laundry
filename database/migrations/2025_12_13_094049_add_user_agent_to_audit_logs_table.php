<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('audit_logs', function (Blueprint $table) {
            // Add user_agent if not exists
            if (!Schema::hasColumn('audit_logs', 'user_agent')) {
                $table->text('user_agent')->nullable()->after('ip_address');
            }
        });

        // Make user_id nullable for system events (separate because of change())
        if (Schema::hasColumn('audit_logs', 'user_id')) {
            // For MySQL
            DB::statement('ALTER TABLE audit_logs MODIFY user_id BIGINT UNSIGNED NULL');
        }

        // Add indexes - using DB statement to avoid errors if index already exists
        $indexes = DB::select("SHOW INDEX FROM audit_logs WHERE Key_name = 'audit_logs_event_index'");
        if (empty($indexes)) {
            Schema::table('audit_logs', function (Blueprint $table) {
                $table->index('event');
            });
        }

        $indexes = DB::select("SHOW INDEX FROM audit_logs WHERE Key_name = 'audit_logs_created_at_index'");
        if (empty($indexes)) {
            Schema::table('audit_logs', function (Blueprint $table) {
                $table->index('created_at');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('audit_logs', function (Blueprint $table) {
            if (Schema::hasColumn('audit_logs', 'user_agent')) {
                $table->dropColumn('user_agent');
            }
        });

        // Drop indexes if they exist
        $indexes = DB::select("SHOW INDEX FROM audit_logs WHERE Key_name = 'audit_logs_event_index'");
        if (!empty($indexes)) {
            Schema::table('audit_logs', function (Blueprint $table) {
                $table->dropIndex(['event']);
            });
        }

        $indexes = DB::select("SHOW INDEX FROM audit_logs WHERE Key_name = 'audit_logs_created_at_index'");
        if (!empty($indexes)) {
            Schema::table('audit_logs', function (Blueprint $table) {
                $table->dropIndex(['created_at']);
            });
        }
    }
};