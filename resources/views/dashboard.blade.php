@extends('layouts.app')

@section('content')

{{-- ════════════════════════════ DASHBOARD AMIGABLE PARA OPERARIOS ════════════════════════════ --}}
<div class="row mb-4">
    <div class="col-12">
        <div class="card-operario bg-white p-4">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <h2 class="section-title mb-0">
                        <i class="bi bi-speedometer2"></i>
                        Panel de Control
                    </h2>
                    <p class="text-muted mb-0 mt-2">
                        <i class="bi bi-person-circle"></i>
                        Bienvenido, <strong>{{ auth()->user()->name }}</strong>
                        <span class="badge bg-warning text-dark ms-2">{{ auth()->user()->role }}</span>
                    </p>
                </div>
                <div class="col-md-4">
                    <div class="text-end">
                        <h5 class="text-muted mb-1">{{ now()->format('d/m/Y') }}</h5>
                        <h6 class="text-muted">{{ now()->format('H:i') }} horas</h6>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- ════════════════════════════ ESTADÍSTICAS RÁPIDAS ════════════════════════════ --}}
<div class="row mb-4">
    <div class="col-md-3 col-6 mb-3">
        <div class="card-operario bg-primary text-white p-4">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h6 class="mb-1">Total Cisternas</h6>
                    <h3 class="mb-0">{{ App\Models\Cisterna::count() }}</h3>
                </div>
                <i class="bi bi-truck" style="font-size: 2.5rem; opacity: 0.7;"></i>
            </div>
        </div>
    </div>
    <div class="col-md-3 col-6 mb-3">
        <div class="card-operario bg-success text-white p-4">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h6 class="mb-1">Consumidas Hoy</h6>
                    <h3 class="mb-0">{{ App\Models\Cisterna::whereDate('FechaConsumoMG', now())->count() }}</h3>
                </div>
                <i class="bi bi-check-circle" style="font-size: 2.5rem; opacity: 0.7;"></i>
            </div>
        </div>
    </div>
    <div class="col-md-3 col-6 mb-3">
        <div class="card-operario bg-warning text-dark p-4">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h6 class="mb-1">Pendientes</h6>
                    <h3 class="mb-0">{{ App\Models\Cisterna::whereNull('FechaConsumoMG')->count() }}</h3>
                </div>
                <i class="bi bi-clock" style="font-size: 2.5rem; opacity: 0.7;"></i>
            </div>
        </div>
    </div>
    <div class="col-md-3 col-6 mb-3">
        <div class="card-operario bg-info text-white p-4">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h6 class="mb-1">Usuarios</h6>
                    <h3 class="mb-0">{{ App\Models\User::count() }}</h3>
                </div>
                <i class="bi bi-people" style="font-size: 2.5rem; opacity: 0.7;"></i>
            </div>
        </div>
    </div>
</div>

{{-- ════════════════════════════ ACCIONES RÁPIDAS ════════════════════════════ --}}
<div class="row mb-4">
    <div class="col-12">
        <div class="card-operario bg-white p-4">
            <h5 class="section-title mb-3">
                <i class="bi bi-lightning"></i>
                Acciones Rápidas
            </h5>
            <div class="row g-3">
                <div class="col-md-3 col-6">
                    <a href="{{ route('cisterna.index') }}" class="btn btn-operario btn-primary w-100">
                        <i class="bi bi-list-ul"></i><br>
                        <small>Ver Cisternas</small>
                    </a>
                </div>
                @if(auth()->user()->isRoot() || auth()->user()->isAdmin() || auth()->user()->isUser())
                    <div class="col-md-3 col-6">
                        <a href="{{ route('cisterna.create') }}" class="btn btn-operario btn-success w-100">
                            <i class="bi bi-plus-circle"></i><br>
                            <small>Nueva Cisterna</small>
                        </a>
                    </div>
                @endif
                <div class="col-md-3 col-6">
                    <a href="{{ route('planificacion.index') }}" class="btn btn-operario btn-info w-100">
                        <i class="bi bi-calendar2-week"></i><br>
                        <small>Planificación</small>
                    </a>
                </div>
                @if(auth()->user()->isAdmin())
                    <div class="col-md-3 col-6">
                        <a href="{{ route('admin.users') }}" class="btn btn-operario btn-warning w-100">
                            <i class="bi bi-people-fill"></i><br>
                            <small>Gestionar Usuarios</small>
                        </a>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

{{-- ════════════════════════════ ACTIVIDAD RECIENTE ════════════════════════════ --}}
<div class="row mb-4">
    <div class="col-md-8">
        <div class="card-operario bg-white p-4">
            <h5 class="section-title mb-3">
                <i class="bi bi-clock-history"></i>
                Cisternas Recientes
            </h5>
            <div class="table-responsive">
                <table class="table table-sm">
                    <thead>
                        <tr>
                            <th>OF</th>
                            <th>Conductor</th>
                            <th>Origen</th>
                            <th>Destino</th>
                            <th>Fecha</th>
                            <th>Estado</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php
                            $recientes = App\Models\Cisterna::orderBy('FechaConsumoMG', 'desc')->take(5)->get();
                        @endphp
                        @forelse($recientes as $cisterna)
                            <tr>
                                <td class="fw-bold">{{ $cisterna->OF }}</td>
                                <td>{{ $cisterna->Conductor }}</td>
                                <td>{{ $cisterna->Origen ?: '—' }}</td>
                                <td>{{ $cisterna->Destino ?: '—' }}</td>
                                <td>{{ $cisterna->FechaConsumoMG?->format('d/m/Y') ?: '—' }}</td>
                                <td>
                                    @if($cisterna->HoraRealConsumoL1 || $cisterna->HoraRealConsumoL2)
                                        <span class="badge bg-success">Consumida</span>
                                    @else
                                        <span class="badge bg-warning">Pendiente</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center text-muted">
                                    No hay cisternas recientes
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card-operario bg-white p-4">
            <h5 class="section-title mb-3">
                <i class="bi bi-info-circle"></i>
                Información del Sistema
            </h5>
            <div class="mb-3">
                <small class="text-muted">Versión del Sistema</small>
                <h6 class="mb-0">Sistema Cisternas v8.0</h6>
            </div>
            <div class="mb-3">
                <small class="text-muted">Última Actualización</small>
                <h6 class="mb-0">{{ now()->format('d/m/Y H:i') }}</h6>
            </div>
            <div class="mb-3">
                <small class="text-muted">Estado del Sistema</small>
                <h6 class="mb-0">
                    <span class="badge bg-success">
                        <i class="bi bi-check-circle"></i> Operativo
                    </span>
                </h6>
            </div>
            <div class="d-grid gap-2">
                <a href="{{ route('profile.edit') }}" class="btn btn-outline-secondary btn-sm">
                    <i class="bi bi-person-gear"></i> Mi Perfil
                </a>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="btn btn-outline-danger btn-sm w-100">
                        <i class="bi bi-power"></i> Cerrar Sesión
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

@endsection
