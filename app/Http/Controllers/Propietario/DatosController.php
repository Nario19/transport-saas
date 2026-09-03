<?php

namespace App\Http\Controllers\Propietario;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class DatosController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        $propietario = $user->propietario;

        if (!$propietario) {
            abort(403, 'No tienes un perfil de propietario asociado.');
        }

        $vehiculos = $propietario->vehiculos()->with(['conductor', 'rutas'])->get();

        return view('users.propietario.datos', compact('propietario', 'vehiculos'));
    }

    public function updateVehiculo(Request $request, \App\Models\Vehiculo $vehiculo)
    {
        $user = auth()->user();
        $propietario = $user->propietario;

        abort_if(!$propietario || $vehiculo->propietario_id !== $propietario->id, 403, 'No autorizado para editar este vehículo.');

        $request->validate([
            'soat_vence'        => 'nullable|date',
            'rev_tecnica_vence' => 'nullable|date',
        ]);

        $vehiculo->update([
            'soat_vence'        => $request->input('soat_vence'),
            'rev_tecnica_vence' => $request->input('rev_tecnica_vence'),
        ]);

        return redirect()->route('propietario.datos')
            ->with('success', "Documentos de la unidad #{$vehiculo->numero_flota} ({$vehiculo->placa_form}) actualizados correctamente.");
    }
}
