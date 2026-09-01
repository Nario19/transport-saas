<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use OwenIt\Auditing\Contracts\Auditable; // la interfaz
use App\Traits\AuditableWithEmpresa;
use Illuminate\Database\Eloquent\SoftDeletes;

class Propietario extends Model implements Auditable
{
    use SoftDeletes, AuditableWithEmpresa, HasFactory;

    protected $fillable = [
        'empresa_id', 'nombre', 'apellidos', 'dni', 'tipo_persona', 'telefono',
        'telefono_alt', 'email', 'direccion', 'activo', 'notas',
        'monto_inicial', 'fecha_monto_inicial',
        'cuota_1', 'fecha_cuota_1',
        'cuota_2', 'fecha_cuota_2',
        'cuota_3', 'fecha_cuota_3',
    ];
    protected $casts = [
        'activo'              => 'boolean',
        'monto_inicial'       => 'float',
        'cuota_1'             => 'float',
        'cuota_2'             => 'float',
        'cuota_3'             => 'float',
        'fecha_monto_inicial' => 'date',
        'fecha_cuota_1'       => 'date',
        'fecha_cuota_2'       => 'date',
        'fecha_cuota_3'       => 'date',
    ];

    // Auditoría: solo campos relevantes
    protected $auditInclude = [
        'nombre','apellidos','dni','tipo_persona','telefono','activo',
        'monto_inicial','fecha_monto_inicial','cuota_1','fecha_cuota_1',
        'cuota_2','fecha_cuota_2','cuota_3','fecha_cuota_3'
    ];

    public function empresa()     { return $this->belongsTo(Empresa::class); }
    public function vehiculos()   { return $this->hasMany(Vehiculo::class); }
    public function conductor()   { return $this->hasOne(Conductor::class, 'dni', 'dni'); }
    public function conductores() { return $this->hasMany(Conductor::class, 'propietario_id'); }

    public function scopeDeEmpresa($q)
    {
        return $q->where('empresa_id', Auth::user()?->empresa_id ?? 0);
    }

    public function getNombreCompletoAttribute(): string
    {
        return trim("{$this->nombre} {$this->apellidos}");
    }

    public function getEsSocioAttribute(): bool
    {
        return ($this->tipo_persona ?? 'personal_normal') === 'socio';
    }

    public function getMontoIngresoTotalAttribute(): float
    {
        if ($this->es_socio) {
            return 0.0;
        }
        if ($this->vehiculos()->count() === 0) {
            return (float) (($this->monto_inicial ?? 0) + ($this->cuota_1 ?? 0) + ($this->cuota_2 ?? 0) + ($this->cuota_3 ?? 0));
        }
        return (float) $this->vehiculos->sum('monto_ingreso_total');
    }

    public function getEstadoIngresoAttribute(): string
    {
        if ($this->es_socio) {
            return 'EXONERADO (SOCIO)';
        }
        if ($this->vehiculos()->count() === 0) {
            return $this->monto_ingreso_total >= 600 ? 'PAGADO' : 'DEUDA';
        }
        return $this->monto_ingreso_deuda > 0 ? 'DEUDA' : 'PAGADO';
    }

    public function getMontoIngresoDeudaAttribute(): float
    {
        if ($this->es_socio) {
            return 0.0;
        }
        if ($this->vehiculos()->count() === 0) {
            return (float) max(0, 600 - $this->monto_ingreso_total);
        }
        return (float) $this->vehiculos->sum('monto_ingreso_deuda');
    }
}
