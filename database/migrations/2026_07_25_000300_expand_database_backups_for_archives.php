<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('database_backups', function (Blueprint $table) {
            $table->string('type', 20)->default('database')->after('trigger');
            $table->string('format', 10)->default('sql')->after('type');
            $table->unsignedBigInteger('record_count')->nullable()->after('size_bytes');
            $table->unsignedBigInteger('photo_count')->nullable()->after('record_count');
            $table->unsignedSmallInteger('manifest_version')->nullable()->after('photo_count');
            $table->index(['fundo_id', 'type', 'created_at']);
        });

        Schema::create('backup_restores', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('fundo_id')->constrained()->cascadeOnDelete();
            $table->foreignId('database_backup_id')->constrained('database_backups')->cascadeOnDelete();
            $table->foreignId('pre_backup_id')->nullable()->constrained('database_backups')->nullOnDelete();
            $table->foreignId('requested_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('mode', 20);
            $table->string('status', 20);
            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamp('failed_at')->nullable();
            $table->text('error_message')->nullable();
            $table->timestamps();
            $table->index(['fundo_id', 'status', 'created_at']);
        });

        $permissionId = DB::table('permisos')->where('modulo', 'ajustes')->where('accion', 'restaurar')->value('id');
        if (! $permissionId) {
            DB::table('permisos')->insert([
                'modulo' => 'ajustes',
                'accion' => 'restaurar',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    public function down(): void
    {
        DB::table('permisos')->where('modulo', 'ajustes')->where('accion', 'restaurar')->delete();
        Schema::dropIfExists('backup_restores');
        Schema::table('database_backups', function (Blueprint $table) {
            $table->dropIndex(['fundo_id', 'type', 'created_at']);
            $table->dropColumn(['type', 'format', 'record_count', 'photo_count', 'manifest_version']);
        });
    }
};
