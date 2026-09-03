<?php

namespace App\Events;

use App\Models\AlertaOperativo;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class AlertaOperativoCreada implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(public AlertaOperativo $alerta) {}

    public function broadcastOn(): array
    {
        return [
            new \Illuminate\Broadcasting\PrivateChannel('empresa.' . $this->alerta->empresa_id . '.operativos'),
        ];
    }

    public function broadcastAs(): string
    {
        return 'operativo.creado';
    }

    public function broadcastWith(): array
    {
        $creatorStr = 'Administración';
        if ($this->alerta->conductor) {
            $veh = $this->alerta->conductor->vehiculos->first();
            $creatorStr = $veh ? "la flota {$veh->numero_flota}" : 'la flota S/N';
        }

        return [
            'alerta' => [
                'id'                => $this->alerta->id,
                'titulo'            => $this->alerta->titulo ?: '⚠️ Alerta en Ruta',
                'punto'             => $this->alerta->punto,
                'mensaje'           => $this->alerta->mensaje,
                'tipo'              => $this->alerta->tipo ?: 'Operativo / Control',
                'visible_conductor' => (bool)$this->alerta->visible_conductor,
                'conductor'         => $this->alerta->conductor?->nombre_completo ?? 'Administración',
                'reportado_por'     => $creatorStr,
                'creado_at'         => $this->alerta->created_at->format('h:i A'),
            ]
        ];
    }
}
