<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Make auditable columns nullable
        DB::statement('ALTER TABLE audit_logs MODIFY auditable_type VARCHAR(255) NULL');
        DB::statement('ALTER TABLE audit_logs MODIFY auditable_id BIGINT UNSIGNED NULL');
    }

    public function down(): void
    {
        DB::statement('ALTER TABLE audit_logs MODIFY auditable_type VARCHAR(255) NOT NULL');
        DB::statement('ALTER TABLE audit_logs MODIFY auditable_id BIGINT UNSIGNED NOT NULL');
    }
};