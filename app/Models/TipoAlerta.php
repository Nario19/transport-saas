<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TipoAlerta extends Model
{
    protected $table = 'tipos_alerta';

    protected $fillable = [
        'empresa_id',
        'nombre',
    ];

    /**
     * Relación con la empresa.
     */
    public function empresa(): BelongsTo
    {
        return $this->belongsTo(Empresa::class);
    }
}
