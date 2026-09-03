<?php

namespace App\Http\Controllers\Propietario;

use App\Http\Controllers\Controller;
use App\Models\Tributo;
use Illuminate\Http\Request;

class TributoController extends Controller
{
    public function index(Request $request)
    {
        $user = auth()->user();
        $propietario = $user->propietario;

        if (!$propietario) {
            abort(403, 'No tienes un perfil de propietario asociado.');
        }

        $vehiculos = $propietario->vehiculos;
        $vehiculoIds = $vehiculos->pluck('id')->toArray();

        // Resumen
        $totalPagadoMes = Tributo::whereIn('vehiculo_id', $vehiculoIds)
            ->whereYear('fecha', now()->year)
            ->whereMonth('fecha', now()->month)
            ->where('estado', 'pagado')
            ->sum('monto');

        $totalPendiente = Tributo::whereIn('vehiculo_id', $vehiculoIds)
            ->where('estado', 'pendiente')
            ->sum('monto');

        // Historial paginado
        $tributos = Tributo::whereIn('vehiculo_id', $vehiculoIds)
            ->with(['vehiculo', 'conductor'])
            ->orderBy('fecha', 'desc')
            ->paginate(15);

        return view('users.propietario.tributos', compact(
            'propietario',
            'vehiculos',
            'tributos',
            'totalPagadoMes',
            'totalPendiente'
        ));
    }
}
