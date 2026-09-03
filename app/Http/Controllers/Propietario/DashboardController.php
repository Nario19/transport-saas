<?php

namespace App\Http\Controllers\Propietario;

use App\Http\Controllers\Controller;
use App\Models\AlertaOperativo;
use App\Models\Sancion;
use App\Models\Tributo;
use App\Models\Vuelta;
use Carbon\Carbon;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        $propietario = $user->propietario;

        if (!$propietario) {
            abort(403, 'No tienes un perfil de propietario asociado.');
        }

        $vehiculos = $propietario->vehiculos()->with(['conductor'])->get();
        $vehiculoIds = $vehiculos->pluck('id')->toArray();
        $hoy = today()->toDateString();
        $mesActual = now()->month;
        $anioActual = now()->year;

        // Vueltas hoy de sus unidades
        $vueltasHoy = Vuelta::whereIn('vehiculo_id', $vehiculoIds)
            ->whereDate('fecha', $hoy)
            ->where('estado', 'completada')
            ->count();

        // Días trabajados en el mes actual
        $diasTrabajadosMes = Vuelta::whereIn('vehiculo_id', $vehiculoIds)
            ->whereYear('fecha', $anioActual)
            ->whereMonth('fecha', $mesActual)
            ->where('estado', 'completada')
            ->distinct()
            ->count('fecha');

        // Vueltas totales de la flota en el mes actual
        $vueltasMes = Vuelta::whereIn('vehiculo_id', $vehiculoIds)
            ->whereYear('fecha', $anioActual)
            ->whereMonth('fecha', $mesActual)
            ->where('estado', 'completada')
            ->count();

        // Tributo de hoy para sus unidades
        $tributosHoy = Tributo::whereIn('vehiculo_id', $vehiculoIds)
            ->whereDate('fecha', $hoy)
            ->with('vehiculo')
            ->get();

        // Deuda acumulada de tributos pendientes
        $deudaTributos = Tributo::whereIn('vehiculo_id', $vehiculoIds)
            ->where('estado', 'pendiente')
            ->sum('monto');

        // Sanciones pendientes
        $sancionesPendientes = Sancion::whereIn('vehiculo_id', $vehiculoIds)
            ->where('estado', 'pendiente')
            ->with(['vehiculo', 'conductor'])
            ->get();

        // Alertas de documentos (SOAT / Revisión Técnica de sus vehículos)
        $alertasDocumentos = [];
        foreach ($vehiculos as $v) {
            if ($v->soat_vence && Carbon::parse($v->soat_vence)->diffInDays(now(), false) >= -15) {
                $alertasDocumentos[] = [
                    'vehiculo' => $v->placa,
                    'tipo'     => 'SOAT',
                    'fecha'    => Carbon::parse($v->soat_vence)->format('d/m/Y'),
                    'vencido'  => Carbon::parse($v->soat_vence)->isPast(),
                ];
            }
            if ($v->rev_tecnica_vence && Carbon::parse($v->rev_tecnica_vence)->diffInDays(now(), false) >= -15) {
                $alertasDocumentos[] = [
                    'vehiculo' => $v->placa,
                    'tipo'     => 'Revisión Técnica',
                    'fecha'    => Carbon::parse($v->rev_tecnica_vence)->format('d/m/Y'),
                    'vencido'  => Carbon::parse($v->rev_tecnica_vence)->isPast(),
                ];
            }
        }

        // Alertas operativas activas de la empresa
        $alertasOperativos = AlertaOperativo::where('empresa_id', $user->empresa_id)
            ->where('estado', 'activo')
            ->where('expires_at', '>', now())
            ->with(['conductor', 'user'])
            ->orderBy('created_at', 'desc')
            ->get();

        return view('users.propietario.dashboard', compact(
            'propietario',
            'vehiculos',
            'vueltasHoy',
            'diasTrabajadosMes',
            'vueltasMes',
            'tributosHoy',
            'deudaTributos',
            'sancionesPendientes',
            'alertasDocumentos',
            'alertasOperativos'
        ));
    }
}
