{{-- DOC: Proyecto Cisternas | Vista personalizada de la aplicacion. --}}
@extends('layouts.app')

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4 class="mb-0"><i class="bi bi-file-earmark-check"></i> Confirmar importación</h4>
        <a href="{{ route('cisterna.bulk') }}" class="btn btn-outline-secondary btn-sm">
            <i class="bi bi-arrow-left"></i> Volver
        </a>
    </div>

    @if(empty($preview))
        <div class="alert alert-warning">No se encontraron hojas válidas en el archivo Excel</div>

    @else
        <div class="card shadow-sm">
            <div class="card-body">
                <p class="text-muted mb-3">
                    Revisa y edita los datos antes de importar. Marca el checkbox las que quieres incluir.
                </p>

                <form id="import-all-form" method="POST" action="{{ route('cisterna.bulk.confirm.store') }}" class="d-none">
                    @csrf
                    <input type="hidden" name="import_all" value="1">
                    <input type="hidden" name="edited_rows_json" id="edited_rows_json_all" value="">
                </form>

                <form id="bulk-edit-form" method="POST" action="{{ route('cisterna.bulk.confirm.store') }}">
                    @csrf
                    <input type="hidden" name="edited_rows_json" id="edited_rows_json_selected" value="">
                    <div class="mb-3 d-flex gap-2">
                        <button type="button" class="btn btn-sm btn-outline-secondary"
                                onclick="toggleTodos(true)">✅ Seleccionar todos</button>
                        <button type="button" class="btn btn-sm btn-outline-secondary"
                                onclick="toggleTodos(false)">❌ Deseleccionar todos</button>
                    </div>

                    <div class="table-responsive">
                        <table class="table table-bordered table-hover align-middle"
                        style="font-size: 0.82rem; white-space:nowrap">
                            <thead style="background: #0f2130; color: #fff;">
                                <tr>
                                    <th>Incluir</th>
                                    <th>Hoja</th>
                                    <th>OF</th>
                                    <th>Nº Cisterna</th>
                                    <th>Conducir</th>
                                    <th>Origen</th>
                                    <th>Destino</th>
                                    <th>Matrícula</th>
                                    <th>Matrícula Cisterna</th>
                                    <th>Transporte</th>
                                    <th>Teléfono</th>
                                    <th>Fecha Consumo</th>
                                    <th title="Hora Estimada Consumo Línea 1">H.E.C L1</th>
                                    <th title="Hora Estimada Consumo Línea 2">H.E.C L2</th>
                                    <th>Global GAP</th>
                                    <th>FDA</th>
                                    <th>Observaciones</th>
                                    <th>Estado</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($preview as $i => $fila)
                                    @php
                                        $error = $fila['_error'] ?? null;
                                        $destinoTexto = trim((string)($fila['Destino'] ?? ''));
                                        $esMoratalla = str_contains(strtolower($destinoTexto), 'moratalla');
                                    @endphp
                                    <tr class="{{ $error ? 'table-warning' : ""}}">

                                        {{-- Checkbox incluir --}}
                                        <td class="text-center">
                                            <input type="checkbox"
                                                    name="filas[{{ $i }}][_incluir]"
                                                    value="1"
                                                    class="form-check-input check-fila"
                                                    {{ $error ? '' : 'checked' }}>
                                        </td>

                                        {{-- Hoja --}}
                                        <td>
                                           <strong>{{$fila['_hoja']}}</strong>
                                            <input type="hidden"
                                                    name="filas[{{ $i }}][_hoja]">
                                        </td>

                                        {{-- OF --}}
                                        <td>
                                            <input type="number"
                                                    name="filas[{{ $i }}][OF]"
                                                    value="{{ $fila['OF'] ?? '' }}"
                                                    class="form-control form-control-sm"
                                                    style="width: 80px">
                                        </td>

                                        {{-- NumeroCisterna --}}
                                        <td>
                                            <input type="number"
                                                    name="filas[{{ $i }}][NumeroCisterna]"
                                                    value="{{ $fila['NumeroCisterna'] ?? '' }}"
                                                    class="form-control form-control-sm"
                                                    style="width: 80px">
                                        </td>

                                        {{-- Conductor --}}
                                        <td>
                                            <input type="text"
                                                    name="filas[{{ $i }}][Conductor]"
                                                    value="{{ $fila['Conductor'] ?? '' }}"
                                                    class="form-control form-control-sm"
                                                    style="min-width: 140px">
                                        </td>

                                        {{-- Origen --}}
                                        <td>
                                            <input type="text"
                                                    name="filas[{{ $i }}][Origen]"
                                                    value="{{ $fila['Origen'] ?? '' }}"
                                                    class="form-control form-control-sm"
                                                    style="min-width: 100px">
                                        </td>

                                        {{-- Destino --}}
                                        <td>
                                            <input type="text"
                                                   name="filas[{{ $i }}][Destino]"
                                                   value="{{ $fila['Destino'] ?? '' }}"
                                                   class="form-control form-control-sm"
                                                   style="min-width: 100px">
                                        </td>

                                        {{-- Matricula --}}
                                        <td>
                                            <input type="text"
                                                   name="filas[{{ $i }}][Matricula]"
                                                   value="{{ $fila['Matricula'] ?? '' }}"
                                                   class="form-control form-control-sm"
                                                   style="min-width: 100px">
                                        </td>

                                        {{-- MatriculaCisterna --}}
                                        <td>
                                            <input type="text"
                                                   name="filas[{{ $i }}][MatriculaCisterna]"
                                                   value="{{ $fila['MatriculaCisterna'] ?? '' }}"
                                                   class="form-control form-control-sm"
                                                   style="min-width: 100px">
                                        </td>

                                        {{-- Transporte --}}
                                        <td>
                                            <input type="text"
                                                   name="filas[{{ $i }}][Transporte]"
                                                   value="{{ $fila['Transporte'] ?? '' }}"
                                                   class="form-control form-control-sm"
                                                   style="min-width: 100px">
                                        </td>

                                        {{-- Telefono --}}
                                        <td>
                                            <input type="text"
                                                   name="filas[{{ $i }}][Telefono]"
                                                   value="{{ $fila['Telefono'] ?? '' }}"
                                                   class="form-control form-control-sm"
                                                   style="min-width: 100px">
                                        </td>

                                        {{-- FechaConsumoMG --}}
                                        <td>
                                            <input type="date"
                                                    name="filas[{{ $i }}][FechaConsumoMG]"
                                                    value="{{ isset($fila['FechaConsumoMG']) && $fila['FechaConsumoMG']
                                                        ? \Carbon\Carbon::createFromFormat('Ymd H:i:s', $fila['FechaConsumoMG'])->format('Y-m-d')
                                                        : ''}}"
                                                    class="form-control form-control-sm"
                                                    style="min-width: 140px">
                                        </td>

                                        {{-- FechaEntradaMG --}}
                                        <td>
                                            <input type="datetime-local"
                                                   name="filas[{{ $i }}][FechaEntradaMG]"
                                                   value="{{ isset($fila['FechaEntradaMG']) && $fila['FechaEntradaMG']
                                                ? \Carbon\Carbon::createFromFormat('Ymd H:i:s', $fila['FechaEntradaMG'])->format('Y-m-d\TH:i')
                                                : '' }}"
                                                   class="form-control form-control-sm"
                                                   style="min-width:160px">
                                        </td>

                                        {{-- HoraEstimadaConsumoL1 --}}
                                        <td>
                                            <input type="time"
                                                    name="filas[{{ $i }}][HoraEstimadaConsumoL1]"
                                                    value="{{ !$esMoratalla && isset($fila['HoraEstimadaConsumoL1']) && $fila['HoraEstimadaConsumoL1']
                                                        ? \Carbon\Carbon::createFromFormat('Ymd H:i:s', $fila['HoraEstimadaConsumoL1'])->format('H:i')
                                                         : '' }}"
                                                    class="form-control form-control-sm hec-11"
                                                    data-index="{{ $i }}"
                                                    style="min-width: 100px">
                                        </td>

                                        {{-- HoraEstimadaConsumoL2 --}}
                                        <td>
                                            <input type="time"
                                                   name="filas[{{ $i }}][HoraEstimadaConsumoL2]"
                                                   value="{{ !$esMoratalla && isset($fila['HoraEstimadaConsumoL2']) && $fila['HoraEstimadaConsumoL2']
                                                        ? \Carbon\Carbon::createFromFormat('Ymd H:i:s', $fila['HoraEstimadaConsumoL2'])->format('H:i')
                                                         : '' }}"
                                                   class="form-control form-control-sm hec-12"
                                                   data-index="{{ $i }}"
                                                   style="min-width: 100px">
                                        </td>

                                        {{-- GlobalGAP --}}
                                        <td class="text-center">
                                            <input type="checkbox"
                                                    name="filas[{{ $i }}][GlobalGAP]"
                                                    value="1"
                                                    class="form-checkbox"
                                                    {{ !empty($fila['GlobalGAP']) ? 'checked' : '' }}>

                                        </td>

                                        {{-- FDA --}}
                                        <td class="text-center">
                                            <input type="checkbox"
                                                   name="filas[{{ $i }}][FDA]"
                                                   value="1"
                                                   class="form-checkbox"
                                                {{ !empty($fila['FDA']) ? 'checked' : '' }}>

                                        </td>

                                        {{-- Observaciones --}}
                                        <td>
                                            <textarea name="filas[{{ $i }}][Observaciones]"
                                                        class="form-control form-control-sm"
                                                        rows="2"
                                                        style="min-width: 200px">{{ $fila['Observaciones'] ?? '' }}</textarea>
                                        </td>

                                        {{-- Estado --}}
                                        <td>
                                            @if($error)
                                                <span class="badge bg-warning text-dark"
                                                      style="cursor:pointer"
                                                      onclick="mostrarError(
                                                          '{{ $fila['OF'] ?? '-' }}',
                                                          '{{ $fila['NumeroCisterna'] ?? '-' }}',
                                                          '{{ addslashes($fila['Conductor']) ?? '-' }}',
                                                          '{{ addslashes($fila['Origen']) ?? '-' }}',
                                                          '{{ addslashes($fila['Destino']) ?? '-' }}',
                                                          '{{ addslashes($fila['_hoja']) ?? '-' }}',
                                                          '{{ addslashes($error) }}'
                                                      )">
                                                    <i class="bi bi-exclamation-triangle"></i> Advertencia
                                                </span>
                                            @else
                                                <span class="badge bg-success">Nueva</span>
                                            @endif
                                        </td>

                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <div class="mt-3 d-flex gap-2">
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-upload"></i> Importar seleccionadas
                        </button>
                        <a href="{{ route('cisterna.bulk') }}" class="btn-outline-secondary">
                            Cancelar
                        </a>
                    </div>
                </form>
            </div>
        </div>
    @endif

    {{-- Modal advertencia cisterna --}}
    <div class="modal fade" id="modalError" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-header bg-warning text-dark">
                <h5 class="modal-title">
                    <i class="bi bi-exclamation-triangle"></i> Advertencia con esta Cisterna
                </h5>
                <button type="button" class="btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <table class="table table-sm mb-0">
                    <tr><th style="width: 140px">OF</th><td id="err-of">-</td></tr>
                    <tr><th>Nº Cisterna</th><td id="err-num">-</td></tr>
                    <tr><th>Conductor</th><td id="err-conductor">-</td></tr>
                    <tr><th>Origen</th><td id="err-origen">-</td></tr>
                    <tr><th>Destino</th><td id="err-destino">-</td></tr>
                    <tr><th>Hoja Excel</th><td id="err-hoja">-</td></tr>
                    <tr><th>Advertencia</th><td id="err-msg" class="text-warning-emphasis fw-bold">-</td></tr>
                </table>
            </div>
            <div class="model-footer">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss>Cerrar</button>
            </div>
        </div>
    </div>

    <script>
        function toggleTodos(checked) {
            document.querySelectorAll('.check-fila').forEach((checkbox) => {
                checkbox.checked = !!checked;
            });
        }

        function collectRowsData() {
            const rows = {};
            const fields = document.querySelectorAll('#bulk-edit-form input[name^="filas["], #bulk-edit-form textarea[name^="filas["], #bulk-edit-form select[name^="filas["]');

            fields.forEach((el) => {
                const match = el.name.match(/^filas\[(\d+)\]\[([^\]]+)\]$/);
                if (!match) return;

                const index = match[1];
                const key = match[2];

                if (!rows[index]) rows[index] = {};

                if (el.type === 'checkbox') {
                    rows[index][key] = el.checked ? '1' : '';
                } else {
                    rows[index][key] = el.value;
                }
            });

            return rows;
        }

        function submitImportAll() {
            const hidden = document.getElementById('edited_rows_json_all');
            hidden.value = JSON.stringify(collectRowsData());
            document.getElementById('import-all-form').submit();
        }

        document.getElementById('bulk-edit-form')?.addEventListener('submit', function () {
            const hidden = document.getElementById('edited_rows_json_selected');
            if (hidden) {
                hidden.value = JSON.stringify(collectRowsData());
            }
        });

        function mostrarError(of, num, conductor, origen, destino, hoja, error) {
            document.getElementById('err-of').textContent        = of;
            document.getElementById('err-num').textContent       = num;
            document.getElementById('err-conductor').textContent = conductor;
            document.getElementById('err-origen').textContent    = origen;
            document.getElementById('err-destino').textContent   = destino;
            document.getElementById('err-hoja').textContent      = hoja;
            document.getElementById('err-msg').textContent       = error;
            new bootstrap.Modal(document.getElementById('modalError')).show();
        }
    </script>
@endsection
