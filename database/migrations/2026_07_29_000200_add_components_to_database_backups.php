<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('database_backups', function (Blueprint $table): void {
            $table->json('components')->nullable()->after('type');
        });
    }

    public function down(): void
    {
        Schema::table('database_backups', function (Blueprint $table): void {
            $table->dropColumn('components');
        });
    }
};
