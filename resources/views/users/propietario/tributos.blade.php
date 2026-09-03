@extends('layouts.propietario')

@section('title', 'Tributos y Pagos')

@section('content')

    {{-- RESUMEN FINANCIERO --}}
    <div class="stat-grid" style="grid-template-columns: 1fr 1fr;">
        <div class="stat-card">
            <div class="stat-val" style="color: var(--green);">S/ {{ number_format($totalPagadoMes, 2) }}</div>
            <div class="stat-lbl">Pagado este Mes</div>
        </div>
        <div class="stat-card">
            <div class="stat-val" style="color: {{ $totalPendiente > 0 ? 'var(--red)' : 'var(--green)' }};">
                S/ {{ number_format($totalPendiente, 2) }}
            </div>
            <div class="stat-lbl">Deuda Pendiente</div>
        </div>
    </div>

    {{-- LISTADO DE TRIBUTOS --}}
    <div class="card">
        <div class="card-header">
            <span class="card-title"><i class="fa-solid fa-receipt" style="color: var(--accent); margin-right: 6px;"></i> Historial de Tributos Diarios</span>
        </div>
        <div class="card-body" style="padding: 12px;">
            @forelse($tributos as $tributo)
                <div style="background: #ffffff; border: 1px solid var(--border); border-radius: 12px; padding: 14px; margin-bottom: 10px; display: flex; justify-content: space-between; align-items: center; box-shadow: var(--shadow);">
                    <div>
                        <div style="font-weight: 800; font-size: 14px; color: var(--text);">
                            {{ $tributo->fecha ? $tributo->fecha->format('d/m/Y') : '---' }}
                        </div>
                        <div style="font-size: 11px; color: var(--text3); margin-top: 2px;">
                            Unidad: <b>{{ $tributo->vehiculo?->placa_form ?? '—' }}</b> (#{{ $tributo->vehiculo?->numero_flota ?? '—' }})
                        </div>
                        @if($tributo->conductor)
                            <div style="font-size: 10.5px; color: var(--text3);">
                                Chofer: {{ $tributo->conductor->nombre_completo }}
                            </div>
                        @endif
                    </div>
                    <div style="text-align: right;">
                        <div style="font-size: 15px; font-weight: 800; color: var(--text); font-family: monospace;">
                            S/ {{ number_format($tributo->monto, 2) }}
                        </div>
                        <div style="margin-top: 4px;">
                            @if($tributo->estado === 'pagado')
                                <span class="pill green" style="font-size: 10px;">
                                    <i class="fa-solid fa-circle-check"></i> Pagado {{ $tributo->metodo_pago ? '('.strtoupper($tributo->metodo_pago).')' : '' }}
                                </span>
                            @elseif($tributo->estado === 'exonerado')
                                <span class="pill gold" style="font-size: 10px;">
                                    <i class="fa-solid fa-shield"></i> Exonerado
                                </span>
                            @else
                                <span class="pill red" style="font-size: 10px;">
                                    <i class="fa-solid fa-clock"></i> Pendiente
                                </span>
                            @endif
                        </div>
                    </div>
                </div>
            @empty
                <div class="empty-state">
                    <i class="fa-solid fa-receipt" style="font-size: 28px; margin-bottom: 8px; display: block; opacity: 0.5;"></i>
                    No hay registros de tributos para tus unidades.
                </div>
            @endforelse

            @if($tributos->hasPages())
                <div style="margin-top: 14px;">
                    {{ $tributos->links() }}
                </div>
            @endif
        </div>
    </div>

@endsection
