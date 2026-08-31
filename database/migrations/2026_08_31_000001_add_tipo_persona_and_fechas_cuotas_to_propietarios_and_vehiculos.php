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
        Schema::table('propietarios', function (Blueprint $table) {
            $table->string('tipo_persona', 30)->default('personal_normal')->after('dni'); // personal_normal, socio
            $table->date('fecha_monto_inicial')->nullable()->after('monto_inicial');
            $table->date('fecha_cuota_1')->nullable()->after('cuota_1');
            $table->date('fecha_cuota_2')->nullable()->after('cuota_2');
            $table->date('fecha_cuota_3')->nullable()->after('cuota_3');
        });

        Schema::table('vehiculos', function (Blueprint $table) {
            $table->date('fecha_monto_inicial')->nullable()->after('monto_inicial');
            $table->date('fecha_cuota_1')->nullable()->after('cuota_1');
            $table->date('fecha_cuota_2')->nullable()->after('cuota_2');
            $table->date('fecha_cuota_3')->nullable()->after('cuota_3');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('propietarios', function (Blueprint $table) {
            $table->dropColumn([
                'tipo_persona',
                'fecha_monto_inicial',
                'fecha_cuota_1',
                'fecha_cuota_2',
                'fecha_cuota_3',
            ]);
        });

        Schema::table('vehiculos', function (Blueprint $table) {
            $table->dropColumn([
                'fecha_monto_inicial',
                'fecha_cuota_1',
                'fecha_cuota_2',
                'fecha_cuota_3',
            ]);
        });
    }
};
