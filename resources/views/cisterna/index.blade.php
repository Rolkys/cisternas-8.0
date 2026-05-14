{{-- DOC: Proyecto Cisternas | Vista personalizada de la aplicación --}}
@extends('layouts.app')

@section('content')

@php
    use Carbon\Carbon;    
@endphp

<div class="d-flex flex-wrap align-items-center gap-3 mb-3">
    {{-- Título a la izquierda --}}
    <h4 class="mb-0">
        <i class="bi bi-list-ul"></i> Listado de Cisternas
    </h4>    


    {{-- Paginación en el centro --}}
    <div class="small mx-auto">
        {{ $cisternas->withQueryString()->links('pagination::bootstrap-4') }}
    </div>

    {{-- Grid de botones con columnas de ancho fijo --}}
    <div class="d-grid gap-2" style="grid-template-columns: repeat(3, 1fr); max-width: 700px;">
            {{-- IMPORTAR EXCEL --}}
            @if(auth()->user()->isRoot() || auth()->user()->isAdmin() || auth()->user()->isUser())
                <a href="{{ route('cisterna.bulk') }}" class="btn btn-outline-success w-100 btn-grid">
                    <i class="bi bi-box-arrow-down-left"></i>
                    <span class="d-none d-md-inline ms-1">Importar Excel</span>
                </a>
            @endif
            
            {{-- CREAR UNA CISTERNA COMPRADA --}}
            @if(auth()->user()->isRoot() || auth()->user()->isAdmin() || auth()->user()->isUser())
                <a href="{{ route('cisterna.create') }}" class="btn btn-outline-warning w-100 btn-grid">
                    <i class="bi bi-plus-lg"></i>
                    <span class="d-none d-md-inline ms-1">Cisterna Comprada</span>
                </a>
            @endif

            {{-- EXPORTAR EXCEL --}}
            @if(auth()->user()->isRoot() || auth()->user()->isAdmin())
                <a href="{{ route('cisterna.export', request()->query()) }}" class="btn btn-outline-primary w-100 btn-grid"> 
                    <i class="bi bi-box-arrow-up"></i>
                    <span class="d-none d-md-inline ms-1">Exportar Excel</span>
                </a>
            @endif

            {{-- DASHBOARD --}}
            @if(auth()->user()->isRoot() || auth()->user()->isAdmin())
                <a href="{{ route('dashboard') }}"
                            class="btn btn-dashboard w-100 btn-grid">
                            <i class="bi bi-speedometer2"></i>
                            <span class="d-none d-md-inline ms-1">Dashboard</span>
                </a>
            @endif

            {{-- PLANIFICACIÓN --}}
            @if(auth()->user()->isRoot() || auth()->user()->isAdmin())
                <a href="{{ route('planificacion.index') }}" class="btn btn-outline-info w-100 btn-grid">
                    <i class="bi bi-calendar2-week"></i>
                    <span class="d-none d-md-inline ms-1">Planificación</span>
                </a>
            @endif

            {{-- ELIMINAR TODAS LAS CISTERNAS --}}
            @if(auth()->user()->isRoot() || auth()->user()->isAdmin())
                <form method="POST" action="{{ route('cisterna.destroyAll') }}" style="display:inline;" 
                      onsubmit="return confirm('¿Estás seguro de que deseas eliminar TODAS las cisternas? Esta acción no se puede deshacer.')">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-outline-danger w-100 btn-grid">
                        <i class="bi bi-trash3"></i>
                        <span class="d-none d-md-inline ms-1">Eliminar Todas</span>
                    </button>
                </form>
            @endif

    </div>

</div>

{{-- Filtros --}}
<form method="GET" action="{{ route('cisterna.index') }}" class="row g-2 mb-3">
    <div class="col-12 col-md-4">
        <input type="text" name="texto" class="form-control"
            placeholder="Buscar conductor, mátricula de la cisterna, origen..."
            value="{{ request('texto') }}">
    </div>
    <div class="col-12 col-md-4">
        <input type="date" name="fecha" class="form-control"
            value="{{ request('fecha') }}">
        
    </div>
    <div class="col-12 col-md-4">
        <input type="number" name="year" class="form-control"
            min="2000" max="2100" step="1" placeholder="Año ej:2026"
            value="{{ request('year') }}">
    </div>
    <div class="col-12 col-md-auto d-flex gap-2">
        <button type="submit" class="btn btn-primary">
            <i class="bi bi-search"></i> Filtrar
        </button>
        <a href="{{ route('cisterna.index') }}" class="btn btn-outline-secondary">
            <i class="bi bi-x-lg"></i> Limpiar
        </a>
    </div>
</form>


{{-- Tabla --}}

<div class="table-responsive">
    {{-- FIX #2: Thead corregido para coincidir con el orden real del tbody --}}
    <table class="table table-bordered table-hover align-middle mb-0 table-index-cisternas"
            style="font-size: 0.82rem; white-space: nowrap;">
        <thead>
            <tr>
                <th>OF</th>
                <th title="Número de Cisterna">Nº Cisterna</th>
                <th>Origen</th>
                <th>Destino</th>
                <th title="Matrícula Camión">Matrícula.T</th>
                <th title="Matrícula Cisterna">Matrícula.C</th>
                <th>Conductor</th>
                <th>Teléfono</th>
                <th title="Fecha Consumo MG">Fecha Consumo</th>
                <th title="Hora estimada de consumo en Línea 1">H.E.C L1</th>
                <th title="Hora real de consumo en Línea 1">H.R.C L1</th>
                <th title="Hora estimada de consumo en Línea 2">H.E.C L2</th>
                <th title="Hora real de consumo en Línea 2">H.R.C L2</th>
                <th title="Food and Drug Administration">FDA</th>
                <th title="Global Partnership for Good Agricultural Practice">GAP</th>
                <th>Estado</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
            @forelse($cisternas as $cisterna)
                 @php
                    $hoy = Carbon::now()->startOfDay();
                    $esTamarite = strpos(strtolower((string) ($cisterna->Destino ?? '')), 'tamarite de litera') !== false;
                    if($esTamarite || $cisterna->HoraRealConsumoL1 || $cisterna->HoraRealConsumoL2){
                        $rowClass = 'row-consumida';
                    }elseif($cisterna->Incidencias){
                        $rowClass = 'row-incidencia';
                    }elseif($cisterna->FechaConsumoMG && $cisterna->FechaConsumoMG->isSameDay($hoy)){
                        $rowClass = 'row-hoy';
                    }elseif($cisterna->FechaConsumoMG && $cisterna->FechaConsumoMG->isAfter($hoy)){
                        $rowClass = 'row-futura';
                    }else{
                        $rowClass = 'row-pendiente';
                    }
                 @endphp
                 <tr class="{{ $rowClass }}">
                    <td>{{ $cisterna->OF }}</td>
                    <td>{{ str_pad($cisterna->NumeroCisterna, 4, '0', STR_PAD_LEFT) }}</td>
                    <td>{{ $cisterna->Origen ?: '-' }}</td>
                    <td>{{ $cisterna->Destino ?: '-' }}</td>
                    <td>{{ $cisterna->Matricula ?: '-' }}</td>
                    <td>{{ $cisterna->MatriculaCisterna ?: '-' }}</td>
                    <td>{{ $cisterna->Conductor }}</td>
                    <td>{{ $cisterna->Telefono ?: '-' }}</td>
                    <td>{{ $cisterna->FechaConsumoMG ? $cisterna->FechaConsumoMG->format('d/m/Y') : '-' }}</td>
                    <td>{{ $cisterna->HoraEstimadaConsumoL1 ? $cisterna->HoraEstimadaConsumoL1->format('H:i'): '--' }}</td>
                    <td>{{ $cisterna->HoraRealConsumoL1 ? $cisterna->HoraRealConsumoL1->format('H:i') : '--' }}</td>
                    <td>{{ $cisterna->HoraEstimadaConsumoL2 ? $cisterna->HoraEstimadaConsumoL2->format('H:i'): '--' }}</td>
                    <td>{{ $cisterna->HoraRealConsumoL2 ? $cisterna->HoraRealConsumoL2->format('H:i'): '--' }}</td>
                    
                    <td>
                        @switch($cisterna->FDA)
                            @case(true)
                                <span class="badge bg-success">Sí</span>
                                @break
                            @case(false)
                                <span class="badge bg-danger">No</span>
                                @break
                            @default
                                <span class="text-muted">-</span>
                        @endswitch
                    </td>
                    

                    <td>
                        @switch($cisterna->GlobalGAP)
                            @case(true)
                                <span class="badge bg-success">Sí</span>
                                @break
                            @case(false)
                                <span class="badge bg-danger">No</span>
                                @break
                            @default
                                <span class="text-muted">-</span>
                        @endswitch
                    </td>
                    
                    <td>
                        @if ($esTamarite || $cisterna->HoraRealConsumoL1 || $cisterna->HoraRealConsumoL2)
                            <span class="badge bg-success">Consumida</span>
                        @elseif($cisterna->Incidencias)
                            <span class="badge bg-danger">Incidencias</span>
                        @elseif($cisterna->FechaConsumoMG && $cisterna->FechaConsumoMG->isSameDay($hoy))
                            <span class="badge bg-warning">Hoy</span>
                        @else
                            <span class="badge bg-secondary">Pendiente</span>
                        @endif
                    </td>
                    
                    <td>
                        <button class="btn btn-sm btn-consumo"
                            title="Registrar consumo"
                            data-id="{{ $cisterna->IdCisterna }}"
                            data-hec-l1="{{ $cisterna->HoraEstimadaConsumoL1 ? $cisterna->HoraEstimadaConsumoL1->format('H:i') : '' }}"
                            data-hrc-l1="{{ optional($cisterna->HoraRealConsumoL1)->format('H:i') ?? '' }}"
                            data-hec-l2="{{ optional($cisterna->HoraEstimadaConsumoL2)->format('H:i') ?? '' }}"
                            data-hrc-l2="{{ optional($cisterna->HoraRealConsumoL2)->format('H:i') ?? '' }}">
                            <i class="bi bi-clock"></i>
                        </button>

                        <a href="{{ route('cisterna.show', $cisterna->IdCisterna) }}"
                            class="btn btn-sm btn-outline-view" title="Ver detalles">
                            <i class="bi bi-eye"></i>
                        </a>
                        @if(!auth()->user()->isOperario())
                            <a href="{{ route('cisterna.edit', $cisterna->IdCisterna) }}"
                                class="btn btn-sm btn-outline-warning" title="Editar">
                                <i class="bi bi-pencil"></i>
                            </a>
                        @endif

                        @if(auth()->user()->isRoot() || auth()->user()->isAdmin())
                            <form method="POST"
                                    action="{{ route('cisterna.destroy', $cisterna->IdCisterna) }}"
                                    style="display:inline"
                                    onsubmit="return confirm('¿Eliminar esta cisterna?')">
                                @csrf
                                @method('DELETE')
                                <button class="btn btn-sm btn-outline-danger" title="Eliminar">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </form>
                        @endif

                    </td>
                 </tr>
            @empty
                <tr>
                    <td colspan="17" class="text-center text-muted">
                        No hay cisternas registradas.
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>

{{-- Paginación inferior --}}
<div class="d-flex justify-content-center mt-3">
    {{ $cisternas->withQueryString()->links('pagination::bootstrap-4') }}
</div>

{{-- Leyenda de colores --}}
<div>
    <span><span class="legend-box" style="background:var(--row-consumida)"></span>Consumida</span>
    <span><span class="legend-box" style="background:var(--row-incidencia)"></span>Incidencia</span>
    <span><span class="legend-box" style="background:var(--row-hoy)"></span>Hoy</span>
    <span><span class="legend-box" style="background:var(--row-futura)"></span>Futura</span>
    <span><span class="legend-box" style="background:var(--row-pendiente)"></span>Pendiente</span>
</div>

{{-- Modal consumo --}}
<div class="modal fade" id="modalConsumo" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <form method="POST" id="form-consumo">
                @csrf
                @method('PATCH')
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="bi bi-clock"></i> Registrar Consumo
                    </h5>
                    <button type="button" class="btn-close btn-close-white" 
                            data-bs-dismiss="modal"></button>
                </div>

                <div class="modal-body">
                    
                    <div class="alert alert-info small py-2">
                        Solo se puede rellenar <strong>L1</strong> o <strong>L2</strong>
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-6">
                            <label class="form-label text-muted small">H. Estimada L1 (ref.)</label>
                            <input type="time" id="info-hec-l1"
                                    class="form-consumo form-control-sm bg-light" readonly>
                        </div>
                        <div class="col-6">
                            <label class="form-label text-muted small">H. Estimada L2 (ref.)</label>
                            <input type="time" id="info-hec-l2"
                                    class="form-consumo form-control-sm bg-light" readonly>
                        </div>
                    </div>

                    <hr>

                    <div class="row g-3 mb-3">
                        <div class="col-6">
                            <label class="form-label fw-bold">H. Real Consumo L1</label>
                            <input type="time" name="HoraRealConsumoL1"
                                    id="hrc-l1" class="form-control">
                        </div>
                        <div class="col-6">
                            <label class="form-label fw-bold">H. Real Consumo L2</label>
                            <input type="time" name="HoraRealConsumoL2"
                                    id="hrc-l2" class="form-control">
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary"><i class="bi bi-save"></i> Guardar</button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- FIX #4: Función abrirModal definida e implementada --}}
@endsection

{{-- Script inline para modal de consumo --}}
<script>
document.addEventListener('DOMContentLoaded', function() {
    console.log('>>> Script inline cargado');
    
    // Manejar clicks en botones de consumo
    document.addEventListener('click', function(e) {
        const btn = e.target.closest('.btn-consumo');
        if (!btn) return;
        
        console.log('>>> Botón consumo clickeado (inline):', btn.dataset);
        
        // Obtener datos del botón
        const id = btn.dataset.id;
        const hecL1 = btn.dataset.hecL1 || btn.dataset.hecl1 || '';
        const hrcL1 = btn.dataset.hrcL1 || btn.dataset.hrcl1 || '';
        const hecL2 = btn.dataset.hecL2 || btn.dataset.hecl2 || '';
        const hrcL2 = btn.dataset.hrcL2 || btn.dataset.hrcl2 || '';
        
        console.log('>>> Datos extraídos:', {id, hecL1, hrcL1, hecL2, hrcL2});
        
        // Configurar formulario
        const form = document.getElementById('form-consumo');
        const l1 = document.getElementById('hrc-l1');
        const l2 = document.getElementById('hrc-l2');
        
        if (!form) {
            console.error('ERROR: No existe #form-consumo');
            alert('Error: No se encontró el formulario de consumo');
            return;
        }
        
        form.action = '/cisterna/' + id + '/consumo';
        
        // Rellenar campos
        const infoL1 = document.getElementById('info-hec-l1');
        const infoL2 = document.getElementById('info-hec-l2');
        if (infoL1) infoL1.value = hecL1;
        if (infoL2) infoL2.value = hecL2;
        if (l1) l1.value = hrcL1;
        if (l2) l2.value = hrcL2;
        
        // Habilitar/deshabilitar campos
        if (l1) l1.disabled = false;
        if (l2) l2.disabled = false;
        if (hrcL1 && l2) l2.disabled = true;
        else if (hrcL2 && l1) l1.disabled = true;
        
        // Abrir modal
        const modalRoot = document.getElementById('modalConsumo');
        if (!modalRoot) {
            console.error('ERROR: No existe #modalConsumo');
            alert('Error: No se encontró el modal de consumo');
            return;
        }
        
        console.log('>>> Abriendo modal (inline)...');
        
        if (typeof bootstrap !== 'undefined') {
            const modal = new bootstrap.Modal(modalRoot);
            modal.show();
        } else {
            // Fallback si Bootstrap no está disponible
            modalRoot.style.display = 'block';
            modalRoot.classList.add('show');
            document.body.classList.add('modal-open');
        }
    });
});
</script>