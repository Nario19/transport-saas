<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Spatie\Permission\Models\Role;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Agregar primer_ingreso a propietarios
        Schema::table('propietarios', function (Blueprint $table) {
            if (!Schema::hasColumn('propietarios', 'primer_ingreso')) {
                $table->boolean('primer_ingreso')->default(true)->after('activo');
            }
        });

        // 2. Agregar propietario_id a users
        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'propietario_id')) {
                $table->foreignId('propietario_id')
                      ->nullable()
                      ->after('conductor_id')
                      ->constrained('propietarios')
                      ->nullOnDelete();
            }
        });

        // 3. Crear rol propietario en Spatie si no existe
        try {
            Role::firstOrCreate(['name' => 'propietario', 'guard_name' => 'web']);
        } catch (\Throwable $e) {}
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'propietario_id')) {
                $table->dropForeign(['propietario_id']);
                $table->dropColumn('propietario_id');
            }
        });

        Schema::table('propietarios', function (Blueprint $table) {
            if (Schema::hasColumn('propietarios', 'primer_ingreso')) {
                $table->dropColumn('primer_ingreso');
            }
        });
    }
};
