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
}
