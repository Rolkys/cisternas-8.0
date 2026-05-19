<?php

/**
 * DOC: Proyecto Cisternas
 * Archivo personalizado del dominio de negocio.
 * Contiene lógica específica de gestion de cisternas/usuarios/planificacion.
 */

namespace App\Http\Controllers;

use App\Models\Cisterna;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

class CisternaController extends Controller
{
    // ==================== INDEX ====================
    /**
     * Muestra el listado principal de registros.
     */
    public function index(Request $request)
    {
        $query = Cisterna::query();
        $year = $this->normalizeYearFilter($request->input('year'));

        if ($year !== null) {
            $query->where(function ($q) use ($year) {
                $q->whereYear('FechaConsumoMG', $year)
                    ->orWhere(function ($q2) use ($year) {
                        $q2->whereNull('FechaConsumoMG')
                            ->whereYear('FechaEntradaMG', $year);
                    });
            });
        } else {
            // Filtro automático por year: muestra el year actual + diciembre del year anterior
            $yearActual = now()->year;
            $query->where(function ($q) use ($yearActual) {
                $q->whereYear('FechaConsumoMG', $yearActual)
                    ->orWhere(function ($q2) use ($yearActual) {
                        $q2->whereYear('FechaConsumoMG', $yearActual - 1)
                            ->whereMonth('FechaConsumoMG', 12);
                    })
                    ->orWhere(function ($q3) use ($yearActual) {
                        $q3->whereNull('FechaConsumoMG')
                            ->whereYear('FechaEntradaMG', $yearActual);
                    })
                    ->orWhere(function ($q4) use ($yearActual) {
                        $q4->whereNull('FechaConsumoMG')
                            ->whereYear('FechaEntradaMG', $yearActual - 1)
                            ->whereMonth('FechaEntradaMG', 12);
                    })
                    ->orWhere(function ($q5) {
                        $q5->whereNull('FechaConsumoMG')
                            ->whereNull('FechaEntradaMG');
                    });
            });
        }

        // Filtro por texto
        if ($request->filled('texto')) {
            $texto = $request->texto;
            $query->where(function ($q) use ($texto) {
                $q->where('conductor', 'like', "%$texto%")
                    ->orWhere('MatriculaCisterna', 'LIKE', "%{$texto}%")
                    ->orWhere('origen', 'like', "%$texto%")
                    ->orWhere('destino', 'like', "%$texto%");
            });
        }

        // Filtro por fecha de consumo
        $fecha = $this->normalizeDateFilter($request->input('fecha'));
        if ($fecha !== null) {
            $query->where(function ($q) use ($fecha) {
                $q->whereDate('FechaConsumoMG', $fecha)
                    ->orWhere(function ($q2) use ($fecha) {
                        $q2->whereNull('FechaConsumoMG')
                            ->whereDate('FechaEntradaMG', $fecha);
                    });
            });
        }

        // ==================== ORDENAMIENTO POR COLUMNA ====================
        $columnasPermitidas = [
            'OF'             => 'OF',
            'NumeroCisterna' => 'NumeroCisterna',
            'Origen'         => 'Origen',
            'Destino'        => 'Destino',
            'Conductor'      => 'Conductor',
            'FechaConsumoMG' => 'FechaConsumoMG',
        ];

        $sortColumn    = $request->get('sort', 'NumeroCisterna');
        $sortDirection = $request->get('direction', 'desc');

        // Validar que la columna y dirección sean válidas
        if (!array_key_exists($sortColumn, $columnasPermitidas)) {
            $sortColumn = 'NumeroCisterna';
        }
        if (!in_array($sortDirection, ['asc', 'desc'])) {
            $sortDirection = 'desc';
        }

        $columnaReal = $columnasPermitidas[$sortColumn];

        // ==================== PAGINACIÓN SIN OFFSET ====================
        $page    = $request->get('page', 1);
        $perPage = 30;
        $total   = (clone $query)->count();

        $allIds  = (clone $query)->orderBy($columnaReal, $sortDirection)->pluck('IdCisterna');
        $pageIds = $allIds->forPage($page, $perPage);

        $cisternas = Cisterna::whereIn('IdCisterna', $pageIds)
                             ->orderBy($columnaReal, $sortDirection)
                             ->get();

        $cisternas = new \Illuminate\Pagination\LengthAwarePaginator(
            $cisternas,
            $total,
            $perPage,
            $page,
            ['path' => $request->url(), 'query' => $request->query()]
        );

        return view('cisterna.index', compact('cisternas', 'sortColumn', 'sortDirection'));
    }

    // ==================== CREATE ====================
    public function create()
    {
        $user = Auth::user();
        if (!$user->isRoot() && !$user->isAdmin() && !$user->isUser()) {
            return redirect()->route('cisterna.index')
                ->with('warning', 'No tienes permisos para crear registros.');
        }
        return view('cisterna.create');
    }

    // ==================== STORE ====================
    public function store(Request $request)
    {
        $user = Auth::user();
        if (!$user->isRoot() && !$user->isAdmin() && !$user->isUser()) {
            return redirect()->route('cisterna.index')
                ->with('warning', 'No tienes permisos para crear registros.');
        }

        $data = $request->validate([
            'OF'                     => 'required|integer',
            'NumeroCisterna'         => 'required|integer',
            'Conductor'              => 'required|string|max:255',
            'Origen'                 => 'nullable|string|max:255',
            'Destino'                => 'nullable|string|max:255',
            'Matricula'              => 'nullable|string|max:255',
            'MatriculaCisterna'      => 'nullable|string|max:255',
            'Teléfono'               => 'nullable|string|max:255',
            'Transporte'             => 'nullable|string|max:255',
            'FechaConsumoMG'         => 'nullable|date',
            'Observaciones'          => 'nullable|string',
            'Incidencias'            => 'nullable|string',
            'GlobalGAP'              => 'nullable|boolean',
            'FDA'                    => 'nullable|boolean',
        ]);

        $data['GlobalGAP'] = $request->boolean('GlobalGAP');
        $data['FDA'] = $request->boolean('FDA');
        $data = $this->syncFechasConsumoEntrada($data);
        $data = $this->autoConsumir($data);
        Cisterna::create($data);

        return redirect()->route('cisterna.index')
                        ->with('success', '✅ Cisterna creada exitosamente.');
    }

    // ==================== SHOW ====================
    public function show(Cisterna $cisterna)
    {
        return view('cisterna.show', compact('cisterna'));
    }

    // ==================== EDIT ====================
    public function edit(Cisterna $cisterna)
    {
        return view('cisterna.edit', compact('cisterna'));
    }

    // ==================== UPDATE ====================
    public function update(Request $request, Cisterna $cisterna)
    {
        $user = Auth::user();

        if ($user->isRoot() || $user->isAdmin()) {
            $data = $request->validate([
                'OF'                => 'required|integer',
                'NumeroCisterna'    => 'required|integer',
                'Conductor'         => 'required|string|max:255',
                'Origen'            => 'nullable|string|max:255',
                'Destino'           => 'nullable|string|max:255',
                'Matricula'         => 'nullable|string|max:50',
                'MatriculaCisterna' => 'nullable|string|max:50',
                'Teléfono'          => 'nullable|string|max:20',
                'Transporte'        => 'nullable|string|max:255',
                'FechaConsumoMG'    => 'nullable|date',
                'Observaciones'     => 'nullable|string',
                'Incidencias'       => 'nullable|string',
                'GlobalGAP'         => 'nullable|boolean',
                'FDA'               => 'nullable|boolean',
            ]);

            $data['GlobalGAP'] = $request->boolean('GlobalGAP');
            $data['FDA'] = $request->boolean('FDA');
            $data = $this->syncFechasConsumoEntrada($data);
            $data = $this->autoConsumir($data, $cisterna);
            $cisterna->update($data);

            return redirect()->route('cisterna.index')
                            ->with('success', '✅ Cisterna actualizada correctamente');
        }

        if ($user->isUser()) {
            $data = $request->validate([
                'OF'                => 'required|integer',
                'NumeroCisterna'    => 'required|integer',
                'Conductor'         => 'required|string|max:255',
                'Origen'            => 'nullable|string|max:255',
                'Destino'           => 'nullable|string|max:255',
                'Matricula'         => 'nullable|string|max:50',
                'MatriculaCisterna' => 'nullable|string|max:50',
                'Teléfono'          => 'nullable|string|max:20',
                'Transporte'        => 'nullable|string|max:255',
                'FechaConsumoMG'    => 'nullable|date',
                'Observaciones'     => 'nullable|string',
                'Incidencias'       => 'nullable|string',
                'GlobalGAP'         => 'nullable|boolean',
                'FDA'               => 'nullable|boolean',
            ]);

            $data['GlobalGAP'] = $request->boolean('GlobalGAP');
            $data['FDA'] = $request->boolean('FDA');
            $data = $this->syncFechasConsumoEntrada($data);
            $data = $this->autoConsumir($data, $cisterna);
            $cisterna->update($data);

            return redirect()->route('cisterna.index')
                            ->with('success', '✅ Cisterna actualizada correctamente');
        }

        if ($user->isOperario()) {
            $request->validate([
                'HoraRealConsumoL1' => 'nullable|date_format:H:i',
                'HoraRealConsumoL2' => 'nullable|date_format:H:i',
                'Observaciones'     => 'nullable|string',
            ]);

            $base = $this->baseConsumptionDate($cisterna);

            if ($request->has('HoraRealConsumoL1')) {
                $cisterna->HoraRealConsumoL1 = $request->HoraRealConsumoL1
                    ? $base . ' ' . $request->HoraRealConsumoL1 . ':00'
                    : null;
            }
            if ($request->has('HoraRealConsumoL2')) {
                $cisterna->HoraRealConsumoL2 = $request->HoraRealConsumoL2
                    ? $base . ' ' . $request->HoraRealConsumoL2 . ':00'
                    : null;
            }
            if ($request->has('Observaciones')) {
                $cisterna->Observaciones = $request->Observaciones;
            }

            $cisterna->save();

            return redirect()->route('cisterna.index')
                            ->with('success', '✅ Consumo actualizado correctamente');
        }

        return redirect()->route('cisterna.index')
            ->with('warning', 'No tienes permisos para editar.');
    }

    // ==================== DESTROY ====================
    public function destroy(Cisterna $cisterna)
    {
        $user = Auth::user();
        if (!$user->isRoot() && !$user->isAdmin()) {
            return redirect()->route('cisterna.index')
                ->with('warning', 'No tienes permisos para eliminar registros.');
        }

        if ($cisterna->Observaciones || $cisterna->Incidencias) {
            session()->flash('deleted_observaciones', $cisterna->Observaciones);
            session()->flash('deleted_incidencias', $cisterna->Incidencias);
            session()->flash('deleted_numero', $cisterna->NumeroCisterna);
        }

        $cisterna->delete();

        return redirect()->route('cisterna.index')
                        ->with('success', '✅ Cisterna eliminada correctamente.');
    }

    // ==================== UPDATE Consumo MODAL ====================
    public function updateConsumo(Request $request, Cisterna $cisterna)
    {
        $request->validate([
            'HoraRealConsumoL1'  => 'nullable|date_format:H:i',
            'HoraRealConsumoL2'  => 'nullable|date_format:H:i',
            'Observaciones'      => 'nullable|string'
        ]);

        $base = $this->baseConsumptionDate($cisterna);

        $user = Auth::user();

        if ($user->isOperario()) {
            $cisterna->HoraRealConsumoL1 = $request->HoraRealConsumoL1
                ? $base . ' ' . $request->HoraRealConsumoL1 . ':00'
                : $cisterna->HoraRealConsumoL1;
            $cisterna->HoraRealConsumoL2 = $request->HoraRealConsumoL2
                ? $base . ' ' . $request->HoraRealConsumoL2 . ':00'
                : $cisterna->HoraRealConsumoL2;
            $cisterna->Observaciones = $request->Observaciones ?? $cisterna->Observaciones;
        } else {
            $cisterna->HoraRealConsumoL1 = $request->HoraRealConsumoL1
                ? $base . ' ' . $request->HoraRealConsumoL1 . ':00'
                : null;
            $cisterna->HoraRealConsumoL2 = $request->HoraRealConsumoL2
                ? $base . ' ' . $request->HoraRealConsumoL2 . ':00'
                : null;
            $cisterna->Observaciones = $request->Observaciones;
        }

        $cisterna->save();

        return redirect()->route('cisterna.index')
                        ->with('success', '✅ Consumo Actualizado Correctamente');
    }

    // ==================== BULK UPLOAD ====================
    public function bulkUpload()
    {
        $user = Auth::user();
        if (!$user->isRoot() && !$user->isAdmin()) {
            return redirect()->route('cisterna.index')
                ->with('warning', 'No tienes permisos para realizar carga masiva.');
        }
        return view('cisterna.bulk');
    }

    public function bulkStore(Request $request)
    {
        $user = Auth::user();
        if (!$user->isRoot() && !$user->isAdmin()) {
            return redirect()->route('cisterna.index')
                ->with('warning', 'No tienes permisos para realizar carga masiva.');
        }

        $request->validate([
            'excel' => 'required|file|mimes:xlsx,xls',
        ]);

        $path     = $request->file('excel')->store('temp');
        $fullPath = storage_path('app/private/' . $path);

        $service = new \App\Services\ExcelImportService();
        $preview = $service->preview($fullPath, true);

        session([
            'bulk_preview'  => $preview,
            'bulk_tempPath' => $path,
        ]);

        return redirect()->route('cisterna.bulk.confirm');
    }

    public function bulkConfirm()
    {
        $user = Auth::user();
        if (!$user->isRoot() && !$user->isAdmin()) {
            return redirect()->route('cisterna.index')
                ->with('warning', 'No tienes permisos para realizar carga masiva.');
        }

        $preview = session('bulk_preview');
        if (!$preview) {
            return redirect()->route('cisterna.bulk')
                            ->with('warning', 'No hay datos pendientes de confirmar.');
        }

        return view('cisterna.bulk_confirm', compact('preview'));
    }

    public function bulkConfirmStore(Request $request)
    {
        $user = Auth::user();
        if (!$user->isRoot() && !$user->isAdmin()) {
            return redirect()->route('cisterna.index')
                ->with('warning', 'No tienes permisos para realizar carga masiva.');
        }

        $tempPath       = session('bulk_tempPath');
        $filas          = $request->input('filas', []);
        $previewSession = session('bulk_preview', []);
        $isImportAll    = $request->boolean('import_all');
        $hasEditedRowsJson = $request->filled('edited_rows_json');

        $imported    = 0;
        $omitidos    = 0;
        $actualizados = 0;

        $postTruncado = !empty($previewSession)
            && (
                (count($filas) > 0 && count($filas) < count($previewSession))
                || ($hasEditedRowsJson && count($filas) !== count($previewSession))
            );

        if ($isImportAll || $postTruncado) {
            if (!$tempPath) {
                return redirect()->route('cisterna.bulk')
                    ->with('warning', 'No hay archivo temporal para importar.');
            }

            $fullPath   = storage_path('app/private/' . $tempPath);
            $service    = new \App\Services\ExcelImportService();
            $preview    = $service->preview($fullPath, true);
            $editedRows = json_decode((string) $request->input('edited_rows_json', '[]'), true);
            if (!is_array($editedRows)) {
                $editedRows = [];
            }

            foreach ($preview as $index => $fila) {
                $filaEditada = $fila;
                if (isset($editedRows[(string) $index]) && is_array($editedRows[(string) $index])) {
                    $filaEditada = array_merge($filaEditada, $editedRows[(string) $index]);
                } elseif (isset($editedRows[$index]) && is_array($editedRows[$index])) {
                    $filaEditada = array_merge($filaEditada, $editedRows[$index]);
                }

                if (!$isImportAll && empty($filaEditada['_incluir'])) {
                    $omitidos++;
                    continue;
                }
                if (!empty($filaEditada['_error'])) {
                    $omitidos++;
                    continue;
                }

                $data = $this->onlyAllowedImportFields($filaEditada);
                $data['GlobalGAP'] = !empty($filaEditada['GlobalGAP']);
                $data['FDA']       = !empty($filaEditada['FDA']);
                $data = $this->normalizeRequiredImportFields($data);

                if (!$this->hasRequiredImportFields($data)) {
                    $omitidos++;
                    continue;
                }

                if (isset($data['Observaciones']) && trim((string) $data['Observaciones']) === '') {
                    $data['Observaciones'] = null;
                }

                $data = $this->syncFechasConsumoEntrada($data);
                $data = $this->normalizeImportConsumptionHours($data);
                $data = $this->autoConsumir($data);

                $existing = Cisterna::where('OF', $data['OF'] ?? null)
                    ->where('NumeroCisterna', $data['NumeroCisterna'] ?? null)
                    ->first();

                if ($existing) {
                    if ($isImportAll) {
                        $existing->update($data);
                        $actualizados++;
                    } else {
                        $omitidos++;
                    }
                } else {
                    Cisterna::create($data);
                    $imported++;
                }
            }

            if ($tempPath) {
                \Storage::delete($tempPath);
            }
            session()->forget(['bulk_preview', 'bulk_tempPath']);

            if (!$isImportAll) {
                return redirect()->route('cisterna.index')
                    ->with('success', "{$imported} cisternas importadas. {$omitidos} omitidas.");
            }

            return redirect()->route('cisterna.index')
                ->with('success', "✅ {$imported} creadas, {$actualizados} actualizadas, {$omitidos} omitidas.");
        }

        foreach ($filas as $fila) {
            if (empty($fila['_incluir'])) {
                $omitidos++;
                continue;
            }

            $fila = $this->normalizeRequiredImportFields($fila);
            if (!$this->hasRequiredImportFields($fila)) {
                $omitidos++;
                continue;
            }

            $existe = Cisterna::where('OF', $fila['OF'])
                              ->where('NumeroCisterna', $fila['NumeroCisterna'])
                              ->exists();
            if ($existe) {
                $omitidos++;
                continue;
            }

            $data = $this->onlyAllowedImportFields($fila);
            $data['GlobalGAP'] = isset($fila['GlobalGAP']) ? (bool) $fila['GlobalGAP'] : false;
            $data['FDA']       = isset($fila['FDA'])       ? (bool) $fila['FDA']       : false;

            if (isset($data['Observaciones']) && trim($data['Observaciones']) === '') {
                $data['Observaciones'] = null;
            }

            $data = $this->syncFechasConsumoEntrada($data);
            $data = $this->normalizeImportConsumptionHours($data);
            $data = $this->autoConsumir($data);

            Cisterna::create($data);
            $imported++;
        }

        if ($tempPath) {
            \Storage::delete($tempPath);
        }
        session()->forget(['bulk_preview', 'bulk_tempPath']);

        return redirect()->route('cisterna.index')
                        ->with('success', "✅ {$imported} cisternas importadas. {$omitidos} omitidas.");
    }

    // ==================== EXPORTAR EXCEL ====================
    public function export(Request $request)
    {
        $query = Cisterna::query();
        $year = $this->normalizeYearFilter($request->input('year'));

        if ($year !== null) {
            $query->where(function ($q) use ($year) {
                $q->whereYear('FechaConsumoMG', $year)
                    ->orWhere(function ($q2) use ($year) {
                        $q2->whereNull('FechaConsumoMG')
                            ->whereYear('FechaEntradaMG', $year);
                    });
            });
        }

        if ($request->filled('texto')) {
            $texto = $request->texto;
            $query->where(function ($q) use ($texto) {
                $q->where('Conductor', 'like', "%$texto%")
                    ->orWhere('Matricula', 'like', "%$texto%")
                    ->orWhere('Origen', 'like', "%$texto%")
                    ->orWhere('Destino', 'like', "%$texto%");
            });
        }

        $fecha = $this->normalizeDateFilter($request->input('fecha'));
        if ($fecha !== null) {
            $query->whereDate('FechaConsumoMG', $fecha);
        }

        $cisternas = $query->orderByDesc('NumeroCisterna')->get();

        $service  = new \App\Services\ExcelExportService();
        $filePath = $service->export($cisternas);

        return response()->download($filePath)->deleteFileAfterSend(true);
    }

    // ==================== DASHBOARD ====================
    public function dashboard(Request $request)
    {
        $desde = $this->normalizeDateFilter($request->input('desde'));
        $hasta = $this->normalizeDateFilter($request->input('hasta'));

        $query = Cisterna::query();

        if ($desde || $hasta) {
            $query->where(function ($q) use ($desde, $hasta) {
                if ($desde) {
                    $q->where(function ($sub) use ($desde) {
                        $sub->whereDate('FechaConsumoMG', '>=', $desde)
                            ->orWhereDate('FechaEntradaMG', '>=', $desde);
                    });
                }
                if ($hasta) {
                    $q->where(function ($sub) use ($hasta) {
                        $sub->whereDate('FechaConsumoMG', '<=', $hasta)
                            ->orWhereDate('FechaEntradaMG', '<=', $hasta);
                    });
                }
            });
        }

        $total      = (clone $query)->count();
        $consumidas = (clone $query)
                        ->where(function ($q) {
                            $q->whereNotNull('HoraRealConsumoL1')
                              ->orWhereNotNull('HoraRealConsumoL2')
                              ->orWhere('Destino', 'like', '%tamarite de litera%');
                        })
                        ->count();
        $pendientes = (clone $query)
                        ->whereNull('Incidencias')
                        ->where(function ($q) {
                            $q->whereNull('HoraRealConsumoL1')
                              ->whereNull('HoraRealConsumoL2')
                              ->where('Destino', 'not like', '%tamarite de litera%');
                        })
                        ->count();
        $incidencias = (clone $query)->whereNotNull('Incidencias')
                                     ->where('Incidencias', '!=', '')
                                     ->count();
        $hoy_count   = Cisterna::whereDate('FechaConsumoMG', today())->count();
        $en_transito = Cisterna::whereNull('FechaEntradaMG')
                                ->whereNull('HoraRealConsumoL1')
                                ->whereNull('HoraRealConsumoL2')
                                ->where('Destino', 'not like', '%tamarite de litera%')
                                ->count();
        $recientes      = Cisterna::orderByDesc('IdCisterna')->take(5)->get();
        $hoy_cisternas  = Cisterna::whereDate('FechaConsumoMG', today())
                                   ->orderBy('HoraEstimadaConsumoL1')
                                   ->get();
        $años = Cisterna::selectRaw('YEAR(COALESCE(FechaConsumoMG, created_at)) as ano')
                ->groupByRaw('YEAR(COALESCE(FechaConsumoMG, created_at))')
                ->orderByRaw('YEAR(COALESCE(FechaConsumoMG, created_at)) DESC')
                ->pluck('ano');

        $añoSeleccionado = $this->normalizeYearFilter($request->input('año'));
        $cisternasDelAño = collect();

        if ($añoSeleccionado) {
            $cisternasDelAño = Cisterna::where(function ($q) use ($añoSeleccionado) {
                $q->whereYear('FechaConsumoMG', $añoSeleccionado)
                  ->orWhere(function ($q2) use ($añoSeleccionado) {
                      $q2->whereNull('FechaConsumoMG')
                         ->whereYear('created_at', $añoSeleccionado);
                  });
            })
            ->orderByDesc('NumeroCisterna')
            ->get();
        }

        return view('cisterna.dashboard', compact(
            'total', 'consumidas', 'hoy_count', 'incidencias',
            'pendientes', 'en_transito', 'recientes', 'hoy_cisternas',
            'años', 'añoSeleccionado', 'cisternasDelAño',
            'desde', 'hasta'
        ));
    }

    // ==================== HELPERS ====================
    private function autoConsumir(array $data, ?Cisterna $cisterna = null): array
    {
        return $data;
    }

    private function syncFechasConsumoEntrada(array $data): array
    {
        $fechaConsumo       = $data['FechaConsumoMG'] ?? null;
        $data['FechaEntradaMG'] = $fechaConsumo ?: null;
        return $data;
    }

    private function normalizeYearFilter($value): ?int
    {
        if (!is_string($value) && !is_numeric($value)) {
            return null;
        }

        $year = trim((string) $value);
        if (!preg_match('/^\d{4}$/', $year)) {
            return null;
        }

        $year = (int) $year;
        return $year >= 2000 && $year <= 2100 ? $year : null;
    }

    private function normalizeDateFilter($value): ?string
    {
        if (!is_string($value) && !is_numeric($value)) {
            return null;
        }

        $date = trim((string) $value);
        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
            return null;
        }

        [$year, $month, $day] = array_map('intval', explode('-', $date));
        return checkdate($month, $day, $year) ? $date : null;
    }

    private function baseConsumptionDate(Cisterna $cisterna): string
    {
        if ($cisterna->FechaConsumoMG) {
            return $cisterna->FechaConsumoMG->format('Y-m-d');
        }

        if ($cisterna->FechaEntradaMG) {
            return $cisterna->FechaEntradaMG->format('Y-m-d');
        }

        return now()->format('Y-m-d');
    }

    private function normalizeImportConsumptionHours(array $data): array
    {
        $keys = [
            'HoraEstimadaConsumoL1',
            'HoraEstimadaConsumoL2',
            'HoraRealConsumoL1',
            'HoraRealConsumoL2',
        ];
        foreach ($keys as $key) {
            if (array_key_exists($key, $data)) {
                $value      = trim((string) $data[$key]);
                $data[$key] = $value === '' ? null : $value;
            }
        }
        return $data;
    }

    private function onlyAllowedImportFields(array $data): array
    {
        return collect($data)->only([
            'OF',
            'NumeroCisterna',
            'Conductor',
            'Telefono',
            'Origen',
            'Destino',
            'Matricula',
            'MatriculaCisterna',
            'Transporte',
            'FechaFabricacionHuelva',
            'HoraSalida',
            'FechaEntradaMG',
            'HoraLlegadaEstimada',
            'FechaConsumoMG',
            'HoraEstimadaConsumoL1',
            'HoraEstimadaConsumoL2',
            'HoraRealConsumoL1',
            'HoraRealConsumoL2',
            'GlobalGAP',
            'FDA',
            'Observaciones',
            'Incidencias',
        ])->toArray();
    }

    private function normalizeRequiredImportFields(array $data): array
    {
        foreach (['OF', 'NumeroCisterna'] as $key) {
            if (array_key_exists($key, $data)) {
                $value = trim((string) $data[$key]);
                $data[$key] = $value !== '' && is_numeric($value) ? (int) $value : null;
            }
        }

        if (array_key_exists('Conductor', $data)) {
            $data['Conductor'] = trim((string) $data['Conductor']);
        }

        return $data;
    }

    private function hasRequiredImportFields(array $data): bool
    {
        return !empty($data['OF'])
            && !empty($data['NumeroCisterna'])
            && trim((string) ($data['Conductor'] ?? '')) !== '';
    }

    public function destroyAll(Request $request)
    {
        $user = auth()->user();

        if (!optional($user)->isRoot()) {
            return redirect()->route('cisterna.index')
                ->with('warning', 'Solo el usuario Root puede eliminar todas las cisternas.');
        }

        $validated = $request->validate([
            'password' => 'required|string',
            'confirmation_text' => 'required|string|in:ELIMINAR TODO',
        ]);

        if (!Hash::check($validated['password'], $user->password)) {
            return redirect()->route('cisterna.index')
                ->with('warning', 'La contrasena no es correcta. No se ha eliminado ninguna cisterna.');
        }

        $deletedCount = Cisterna::count();

        Log::warning('Eliminacion total de cisternas ejecutada', [
            'user_id' => $user->getKey(),
            'user_email' => $user->email,
            'deleted_count' => $deletedCount,
        ]);

        Cisterna::truncate();

        return redirect()->route('cisterna.index')
            ->with('success', "Se han eliminado {$deletedCount} cisternas correctamente.");
    }
}
