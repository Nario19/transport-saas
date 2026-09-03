<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

class RutaParadero extends Model
{
    protected $table = 'ruta_paraderos';

    protected $fillable = [
        'ruta_id',
        'nombre',
        'tipo',
        'orden',
        'latitud_a',
        'longitud_a',
        'latitud_b',
        'longitud_b',
        'tolerancia',
    ];

    protected $casts = [
        'latitud_a'  => 'float',
        'longitud_a' => 'float',
        'latitud_b'  => 'float',
        'longitud_b' => 'float',
        'tolerancia' => 'integer',
    ];

    public function ruta()
    {
        return $this->belongsTo(Ruta::class);
    }

    /**
     * Retorna la lista de paraderos de llegada válidos al iniciar viaje desde un paradero de salida.
     *
     * Regla de Negocio:
     * Si el paradero de salida es de tipo 'intermedio', se bloquean los terminales (origen o destino)
     * que se encuentren más cercanos a dicho paradero en la secuencia de la ruta.
     * El conductor solo puede finalizar el viaje dirigiéndose hacia el extremo opuesto.
     *
     * @param int $rutaId
     * @param int|null $paraderoSalidaId
     * @return \Illuminate\Support\Collection
     */
    public static function paraderosLlegadaValidos(int $rutaId, ?int $paraderoSalidaId): Collection
    {
        $paraderos = self::where('ruta_id', $rutaId)->orderBy('orden')->orderBy('id')->get();

        if ($paraderos->isEmpty()) {
            return collect();
        }

        // Asignar orden secuencial normalizado
        $paraderos = $paraderos->values()->map(function ($p, $idx) {
            $p->posicion_secuencia = ($p->orden !== null && $p->orden > 0) ? (int) $p->orden : ($idx + 1);
            return $p;
        });

        if (!$paraderoSalidaId) {
            return $paraderos;
        }

        $salida = $paraderos->firstWhere('id', $paraderoSalidaId);
        if (!$salida) {
            return $paraderos;
        }

        // Si salió de un paradero terminal (origen o destino), solo se excluye a sí mismo
        if ($salida->tipo !== 'intermedio') {
            return $paraderos->where('id', '!=', $salida->id)->values();
        }

        // Si salió de un punto INTERMEDIO:
        $origenes = $paraderos->where('tipo', 'origen');
        $destinos = $paraderos->where('tipo', 'destino');

        $distOrigen = $origenes->isEmpty() ? INF : $origenes->min(fn($p) => abs($p->posicion_secuencia - $salida->posicion_secuencia));
        $distDestino = $destinos->isEmpty() ? INF : $destinos->min(fn($p) => abs($p->posicion_secuencia - $salida->posicion_secuencia));

        $prohibidosIds = collect([$salida->id]);

        if ($distOrigen < $distDestino) {
            // Está más cerca del extremo origen -> Prohibir volver a los paraderos de origen
            $prohibidosIds = $prohibidosIds->merge($origenes->pluck('id'));
        } elseif ($distDestino < $distOrigen) {
            // Está más cerca del extremo destino -> Prohibir volver a los paraderos de destino
            $prohibidosIds = $prohibidosIds->merge($destinos->pluck('id'));
        }

        return $paraderos->reject(fn($p) => $prohibidosIds->contains($p->id))->values();
    }
}