<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('alertas_operativos', function (Blueprint $table) {
            if (!Schema::hasColumn('alertas_operativos', 'titulo')) {
                $table->string('titulo')->nullable()->after('punto');
            }
            if (!Schema::hasColumn('alertas_operativos', 'mensaje')) {
                $table->text('mensaje')->nullable()->after('titulo');
            }
            if (!Schema::hasColumn('alertas_operativos', 'tipo')) {
                $table->string('tipo')->default('operativo')->after('mensaje'); // 'operativo', 'informativa', 'urgente', 'desvio'
            }
            if (!Schema::hasColumn('alertas_operativos', 'visible_conductor')) {
                $table->boolean('visible_conductor')->default(true)->after('tipo');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('alertas_operativos', function (Blueprint $table) {
            $columns = [];
            if (Schema::hasColumn('alertas_operativos', 'visible_conductor')) {
                $columns[] = 'visible_conductor';
            }
            if (Schema::hasColumn('alertas_operativos', 'tipo')) {
                $columns[] = 'tipo';
            }
            if (Schema::hasColumn('alertas_operativos', 'mensaje')) {
                $columns[] = 'mensaje';
            }
            if (Schema::hasColumn('alertas_operativos', 'titulo')) {
                $columns[] = 'titulo';
            }
            if (!empty($columns)) {
                $table->dropColumn($columns);
            }
        });
    }
};
