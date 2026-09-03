<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable; // la interfaz
use App\Traits\AuditableWithEmpresa;
use Illuminate\Database\Eloquent\SoftDeletes;

class Vuelta extends Model implements Auditable
{
    use SoftDeletes, AuditableWithEmpresa;

    protected $fillable = [
        'empresa_id','vehiculo_id','conductor_id','ruta_id','paradero_salida_id','paradero_llegada_id','created_by',
        'fecha', 'numero_vuelta', 'hora_salida', 'hora_llegada', 'observaciones',
        'latitud', 'longitud', 'lat_actual', 'lng_actual', 'latitud_fin', 'longitud_fin', 'estado',
    ];
    protected $casts = [
        'fecha'        => 'date',
        'latitud'      => 'decimal:7',
        'longitud'     => 'decimal:7',
        'lat_actual'   => 'decimal:7',
        'lng_actual'   => 'decimal:7',
        'latitud_fin'  => 'decimal:7',
        'longitud_fin' => 'decimal:7',
    ];
    protected $auditInclude = ['vehiculo_id','conductor_id','ruta_id','fecha','numero_vuelta'];

    public function empresa()    { return $this->belongsTo(Empresa::class); }
    public function vehiculo()   { return $this->belongsTo(Vehiculo::class); }
    public function conductor()  { return $this->belongsTo(Conductor::class); }
    public function ruta()       { return $this->belongsTo(Ruta::class); }
    public function paraderoSalida() { return $this->belongsTo(RutaParadero::class, 'paradero_salida_id'); }
    public function paraderoLlegada() { return $this->belongsTo(RutaParadero::class, 'paradero_llegada_id'); }
    public function creadoPor()  { return $this->belongsTo(User::class, 'created_by'); }

    public function scopeDeEmpresa($q)
    {
        return $q->where('empresa_id', Auth::user()?->empresa_id ?? 0);
    }
    public function scopeHoy($q)       { return $q->whereDate('fecha', today()); }
    public function scopeDelDia($q, $fecha) { return $q->whereDate('fecha', $fecha); }
    public function scopeActivas($q)   { return $q->where('estado', 'activa'); }
    public function scopeCompletadas($q) { return $q->where('estado', 'completada'); }

    /**
     * Determina el color y estilo del estado de la vuelta según los paraderos recorridos.
     * 
     * Reglas de Negocio:
     * - ACTIVA: Estado activa en ruta.
     * - VERDE: De Punto de Origen a Destino (o viceversa) -> Extremo a Extremo completo.
     * - ROJO: Tramos cortos / 1 solo salto consecutivo (ej: A->B, B->C, C->D).
     * - NARANJA: Tramos medios de 2 o más saltos pero sin completar la ruta entera (ej: A->C, B->D, B->E).
     */
    public function getBadgeEstadoAttribute(): array
    {
        if ($this->estado === 'activa') {
            return [
                'label'     => 'ACTIVA',
                'bg'        => 'var(--green-l)',
                'color'     => 'var(--green)',
                'border'    => '#86efac',
                'categoria' => 'activa',
            ];
        }

        $salida = $this->paraderoSalida;
        $llegada = $this->paraderoLlegada;

        if (!$salida || !$llegada) {
            return [
                'label'     => 'COMPLETADA',
                'bg'        => '#dcfce7',
                'color'     => '#15803d',
                'border'    => '#86efac',
                'categoria' => 'verde',
            ];
        }

        // Obtener la secuencia de paraderos de la ruta
        $rutaId = $this->ruta_id;
        $paraderos = RutaParadero::where('ruta_id', $rutaId)->orderBy('orden')->orderBy('id')->get();

        if ($paraderos->isEmpty()) {
            return [
                'label'     => 'COMPLETADA',
                'bg'        => '#dcfce7',
                'color'     => '#15803d',
                'border'    => '#86efac',
                'categoria' => 'verde',
            ];
        }

        // Mapear posiciones secuenciales
        $posMap = [];
        foreach ($paraderos->values() as $idx => $p) {
            $posMap[$p->id] = ($p->orden !== null && $p->orden > 0) ? (int) $p->orden : ($idx + 1);
        }

        $posSalida = $posMap[$salida->id] ?? 1;
        $posLlegada = $posMap[$llegada->id] ?? 1;
        $salto = abs($posSalida - $posLlegada);

        // 1. Caso VERDE: Extremo terminal a extremo terminal (Origen <-> Destino)
        $esExtremoAExtremo = (
            ($salida->tipo === 'origen' && $llegada->tipo === 'destino') ||
            ($salida->tipo === 'destino' && $llegada->tipo === 'origen')
        );

        // Si los tipos no están estrictamente marcados como origen/destino pero no son intermedios
        if (!$esExtremoAExtremo && $salida->tipo !== 'intermedio' && $llegada->tipo !== 'intermedio' && $salida->tipo !== $llegada->tipo) {
            $esExtremoAExtremo = true;
        }

        if ($esExtremoAExtremo) {
            return [
                'label'     => 'COMPLETADA',
                'bg'        => '#dcfce7',
                'color'     => '#15803d',
                'border'    => '#86efac',
                'categoria' => 'verde',
            ];
        }

        // 2. Caso ROJO: 1 solo salto consecutivo (ej: A->B, B->C, C->D)
        if ($salto <= 1) {
            return [
                'label'     => 'COMPLETADA',
                'bg'        => '#fee2e2',
                'color'     => '#b91c1c',
                'border'    => '#fca5a5',
                'categoria' => 'rojo',
            ];
        }

        // 3. Caso NARANJA: 2 o más saltos intermedios (ej: A->C, B->D, B->E)
        return [
            'label'     => 'COMPLETADA',
            'bg'        => '#ffedd5',
            'color'     => '#c2410c',
            'border'    => '#fed7aa',
            'categoria' => 'naranja',
        ];
    }
}
