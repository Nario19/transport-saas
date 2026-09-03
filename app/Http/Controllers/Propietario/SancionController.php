<?php

namespace App\Http\Controllers\Propietario;

use App\Http\Controllers\Controller;
use App\Models\Sancion;
use Illuminate\Http\Request;

class SancionController extends Controller
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

        $totalPendiente = Sancion::whereIn('vehiculo_id', $vehiculoIds)
            ->where('estado', 'pendiente')
            ->sum('monto');

        $sanciones = Sancion::whereIn('vehiculo_id', $vehiculoIds)
            ->with(['vehiculo', 'conductor'])
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        return view('users.propietario.sanciones', compact(
            'propietario',
            'vehiculos',
            'sanciones',
            'totalPendiente'
        ));
    }
}
