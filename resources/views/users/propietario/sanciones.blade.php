@extends('layouts.propietario')

@section('title', 'Sanciones e Infracciones')

@section('content')

    {{-- RESUMEN SANCIONES --}}
    <div class="card" style="background: #ffffff; border-left: 4px solid var(--orange);">
        <div class="card-body" style="display: flex; justify-content: space-between; align-items: center; padding: 14px 16px;">
            <div>
                <div style="font-size: 11px; text-transform: uppercase; font-weight: 700; color: var(--text3);">Total Sanciones Pendientes</div>
                <div style="font-size: 20px; font-weight: 800; color: {{ $totalPendiente > 0 ? 'var(--red)' : 'var(--green)' }};">
                    S/ {{ number_format($totalPendiente, 2) }}
                </div>
            </div>
            <div style="font-size: 28px; color: var(--orange); opacity: 0.8;">
                <i class="fa-solid fa-triangle-exclamation"></i>
            </div>
        </div>
    </div>

    {{-- LISTADO DE SANCIONES --}}
    <div class="card">
        <div class="card-header">
            <span class="card-title"><i class="fa-solid fa-gavel" style="color: var(--accent); margin-right: 6px;"></i> Historial de Sanciones</span>
        </div>
        <div class="card-body" style="padding: 12px;">
            @forelse($sanciones as $sancion)
                <div style="background: #ffffff; border: 1px solid var(--border); border-radius: 12px; padding: 14px; margin-bottom: 10px; box-shadow: var(--shadow);">
                    <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 6px;">
                        <div>
                            <div style="font-weight: 800; font-size: 14px; color: var(--text);">
                                {{ $sancion->motivo ?? 'Infracción Operativa' }}
                            </div>
                            <div style="font-size: 11px; color: var(--text3); margin-top: 2px;">
                                Fecha: {{ $sancion->fecha ? $sancion->fecha->format('d/m/Y') : $sancion->created_at->format('d/m/Y') }}
                            </div>
                        </div>
                        <div style="text-align: right;">
                            <div style="font-size: 15px; font-weight: 800; color: var(--red); font-family: monospace;">
                                S/ {{ number_format($sancion->monto, 2) }}
                            </div>
                            <div style="margin-top: 2px;">
                                @if($sancion->estado === 'pagada')
                                    <span class="pill green" style="font-size: 10px;">Pagada</span>
                                @elseif($sancion->estado === 'exonerada')
                                    <span class="pill gold" style="font-size: 10px;">Exonerada</span>
                                @else
                                    <span class="pill red" style="font-size: 10px;">Pendiente</span>
                                @endif
                            </div>
                        </div>
                    </div>

                    <div style="background: #f8fafc; padding: 8px 10px; border-radius: 6px; font-size: 11.5px; color: var(--text2); display: flex; justify-content: space-between; align-items: center;">
                        <span><i class="fa-solid fa-van-shuttle" style="color: var(--accent);"></i> Unidad: <b>{{ $sancion->vehiculo?->placa_form ?? '—' }}</b></span>
                        @if($sancion->conductor)
                            <span><i class="fa-solid fa-user"></i> Chofer: {{ $sancion->conductor->nombre_completo }}</span>
                        @endif
                    </div>
                </div>
            @empty
                <div class="empty-state">
                    <i class="fa-solid fa-shield-halved" style="font-size: 28px; margin-bottom: 8px; display: block; color: var(--green);"></i>
                    ¡Excelente! No tienes sanciones registradas para tus unidades.
                </div>
            @endforelse

            @if($sanciones->hasPages())
                <div style="margin-top: 14px;">
                    {{ $sanciones->links() }}
                </div>
            @endif
        </div>
    </div>

@endsection
