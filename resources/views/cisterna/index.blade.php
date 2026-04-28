{{-- DOC: Proyecto Cisternas | Vista amigable para operarios - Sistema de Gestión de Cisternas --}}
@extends('layouts.app')

@section('content')

{{-- ════════════════════════════ CABECERA CON ACCIONES RÁPIDAS ════════════════════════════ --}}
<div class="row mb-4">
    <div class="col-12">
        <div class="card-operario bg-white p-4">
            <div class="row align-items-center">
                <div class="col-md-6">
                    <h2 class="section-title mb-0">
                        <i class="bi bi-truck"></i>
                        Gestión de Cisternas
                    </h2>
                    <p class="text-muted mb-0 mt-2">
                        <i class="bi bi-info-circle"></i>
                        Total de cisternas: <strong>{{ $cisternas->total() }}</strong>
                    </p>
                </div>
                <div class="col-md-6">
                    <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                        @if(auth()->user()->isRoot() || auth()->user()->isAdmin() || auth()->user()->isUser())
                            <a href="{{ route('cisterna.create') }}" class="btn btn-operario btn-success">
                                <i class="bi bi-plus-circle-fill"></i> Nueva Cisterna
                            </a>
                        @endif
                        @if(auth()->user()->isRoot() || auth()->user()->isAdmin() || auth()->user()->isUser())
                            <a href="{{ route('cisterna.bulk') }}" class="btn btn-operario btn-info">
                                <i class="bi bi-file-earmark-excel-fill"></i> Importar Excel
                            </a>
                        @endif
                        <a href="{{ route('cisterna.export', request()->query()) }}" class="btn btn-operario btn-primary">
                            <i class="bi bi-download"></i> Exportar
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- ════════════════════════════ NAVEGACIÓN RÁPIDA ════════════════════════════ --}}
<div class="row mb-4">
    <div class="col-12">
        <div class="card-operario bg-light p-3">
            <div class="row g-3">
                <div class="col-md-3 col-6">
                    <a href="{{ route('dashboard') }}" class="btn btn-operario btn-outline-primary w-100">
                        <i class="bi bi-speedometer2"></i><br>
                        <small>Dashboard</small>
                    </a>
                </div>
                <div class="col-md-3 col-6">
                    <a href="{{ route('planificacion.index') }}" class="btn btn-operario btn-outline-info w-100">
                        <i class="bi bi-calendar2-week"></i><br>
                        <small>Planificación</small>
                    </a>
                </div>
                @if(auth()->user()->isAdmin())
                    <div class="col-md-3 col-6">
                        <a href="{{ route('admin.users') }}" class="btn btn-operario btn-outline-warning w-100">
                            <i class="bi bi-people-fill"></i><br>
                            <small>Usuarios</small>
                        </a>
                    </div>
                @endif
                @if(auth()->user()->isRoot() || auth()->user()->isAdmin())
                    <div class="col-md-3 col-6">
                        <form method="POST" action="{{ route('cisterna.destroyAll') }}" onsubmit="return confirmarAccion('¿Eliminar TODAS las cisternas? Esta acción no se puede deshacer.')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-operario btn-outline-danger w-100">
                                <i class="bi bi-trash3-fill"></i><br>
                                <small>Eliminar Todo</small>
                            </button>
                        </form>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

{{-- ════════════════════════════ FILTROS DE BÚSQUEDA AMIGABLES ════════════════════════════ --}}
<div class="row mb-4">
    <div class="col-12">
        <div class="card-operario bg-white p-4">
            <h5 class="section-title mb-3">
                <i class="bi bi-funnel"></i>
                Filtros de Búsqueda
            </h5>
            <form method="GET" action="{{ route('cisterna.index') }}">
                <div class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label fw-bold">
                            <i class="bi bi-search"></i> Buscar
                        </label>
                        <input type="text" name="texto" class="form-control form-control-lg" 
                               placeholder="Matrícula, conductor, origen..." value="{{ request('texto') }}">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label fw-bold">
                            <i class="bi bi-calendar-date"></i> Fecha
                        </label>
                        <input type="date" name="fecha" class="form-control form-control-lg" 
                               value="{{ request('fecha') }}">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label fw-bold">
                            <i class="bi bi-calendar3"></i> Año
                        </label>
                        <input type="number" name="year" class="form-control form-control-lg"
                               min="2000" max="2100" placeholder="Ej: 2026" value="{{ request('year') }}">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-bold">&nbsp;</label>
                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-operario btn-primary flex-fill">
                                <i class="bi bi-search"></i> Buscar
                            </button>
                            <a href="{{ route('cisterna.index') }}" class="btn btn-operario btn-secondary flex-fill">
                                <i class="bi bi-arrow-clockwise"></i> Limpiar
                        @else
                            <span class="badge bg-warning text-dark">Pendiente</span>
                        @endif
                    </td>
                    <td>
                        <button class="btn btn-sm btn-consumo"
                                title="Registrar consumo"
                                data-id="{{ $cisterna->IdCisterna }}"
                                data-hec-l1="{{ $cisterna->HoraEstimadaConsumoL1?->format('H:i') ?? '' }}"
                                data-hrc-l1="{{ $cisterna->HoraRealConsumoL1?->format('H:i') ?? '' }}"
                                data-hec-l2="{{ $cisterna->HoraEstimadaConsumoL2?->format('H:i') ?? '' }}"
                                data-hrc-l2="{{ $cisterna->HoraRealConsumoL2?->format('H:i') ?? '' }}">
                            <i class="bi bi-clock"></i>
                        </button>
                        <a href="{{ route('cisterna.show', $cisterna->IdCisterna) }}"
                            class="btn btn-sm btn-outline-view" title="Ver">
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
                    <td colspan="15" class="text-center text-muted">
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
<div class="mt-3 d-flex flex-wrap gap-3 small text-muted">
    <span><span class="legend-box" style="background:var(--row-consumida)"></span>Consumida</span>
    <span><span class="legend-box" style="background:var(--row-incidencia)"></span>Incidencia</span>
    <span><span class="legend-box" style="background:var(--row-hoy)"></span>Hoy</span>
    <span><span class="legend-box" style="background:var(--row-futura)"></span>Futura</span>
    <span><span class="legend-box" style="background:var(--row-pendiente)"></span>Pendiente</span>
</div>

{{-- Modal consumo --}}
<div class="modal fade" id="modalConsumo" tabindex="-1">
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
                        Solo se puede rellenar <strong>L1</strong> o <strong>L2</strong>, no ambas.
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-6">
                            <label class="form-label text-muted small">H. Estimada L1 (ref.)</label>
                            <input type="time" id="info-hec-l1"
                                    class="form-control form-control-sm bg-light" readonly>
                        </div>
                        <div class="col-6">
                            <label class="form-label text-muted small">H. Estimada L2 (ref.)</label>
                            <input type="time" id="info-hec-l2"
                                    class="form-control form-control-sm bg-light" readonly>
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
                    <button type="button" class="btn btn-outline-secondary"
                            data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-save"></i> Guardar
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('.btn-consumo').forEach(function (btn) {
        btn.addEventListener('click', function () {
            abrirModal(
                this.dataset.id,
                this.dataset.hecL1,
                this.dataset.hrcL1,
                this.dataset.hecL2,
                this.dataset.hrcL2
            );
        });
    });
});
</script>

@endsection
