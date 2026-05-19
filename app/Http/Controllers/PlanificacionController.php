<?php

/**
 * PlanificacionController - Gestion de planificacion de cisternas.
 * 
 * Maneja la planificacion temporal de cisternas mediante almacenamiento
 * en archivo JSON. Permite crear, editar y eliminar filas de planificacion
 * que posteriormente pueden exportarse a Excel.
 */

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class PlanificacionController extends Controller
{
    /**
     * Nombre del archivo JSON de almacenamiento.
     */
    private const ARCHIVO_JSON = 'planificacion.json';

    /**
     * Columnas del Excel de planificacion.
     */
    private const COLUMNAS_EXCEL = [
        'A' => 'Nº Cisterna',
        'B' => 'Destino',
        'C' => 'Fecha Consumo',
        'D' => 'Fecha Fab. Huelva',
        'E' => 'H.E.C L1',
        'F' => 'H.E.C L2',
    ];

    /**
     * Colores para estilos del Excel.
     */
    private const COLOR_CABECERA_FONDO = 'FF0F2130';
    private const COLOR_TEXTO_BLANCO = 'FFFFFFFF';

    /**
     * Formato de fecha para nombres de archivo.
     */
    private const FORMATO_FECHA_ARCHIVO = 'Y-m-d_H-i';

    /**
     * Ruta completa al archivo JSON de planificacion.
     */
    private $rutaJson;

    /**
     * Constructor: inicializa la ruta del archivo JSON.
     */
    public function __construct()
    {
        $this->rutaJson = storage_path('app/' . self::ARCHIVO_JSON);
    }

    // ==================== GESTION JSON ====================

    /**
     * Lee las filas de planificacion desde el archivo JSON.
     *
     * @return array Filas de planificacion o array vacio si no existe
     */
    private function leerFilas(): array
    {
        if (!file_exists($this->rutaJson)) {
            return [];
        }

        $contenido = file_get_contents($this->rutaJson);
        $filas = json_decode($contenido, true);

        return is_array($filas) ? $filas : [];
    }

    /**
     * Guarda las filas de planificacion en el archivo JSON.
     *
     * @param array $filas Filas a guardar
     */
    private function guardarFilas(array $filas): void
    {
        $directorio = dirname($this->rutaJson);
        
        if (!file_exists($directorio)) {
            mkdir($directorio, 0755, true);
        }

        file_put_contents(
            $this->rutaJson, 
            json_encode($filas, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)
        );
    }

    // ==================== CRUD ====================

    /**
     * Muestra la lista de filas de planificacion.
     *
     * @return \Illuminate\View\View Vista con las filas cargadas
     */
    public function index()
    {
        $filas = $this->leerFilas();
        return view('planificacion.index', compact('filas'));
    }

    /**
     * Almacena una nueva fila de planificacion.
     *
     * @param Request $request Datos de la nueva fila
     * @return \Illuminate\Http\RedirectResponse Redireccion con mensaje
     */
    public function store(Request $request)
    {
        if (!$this->verificarPermisoAdmin()) {
            return redirect()->route('planificacion.index')
                ->with('warning', 'Solo administradores pueden modificar la planificacion.');
        }

        $datosValidados = $request->validate([
            'NumeroCisterna'         => 'required|integer',
            'Destino'                => 'nullable|string|max:255',
            'FechaConsumo'           => 'nullable|date',
            'FechaFabricacionHuelva' => 'nullable|date',
            'HoraEstimadaConsumoL1'  => 'nullable|date_format:H:i',
            'HoraEstimadaConsumoL2'  => 'nullable|date_format:H:i',
        ]);

        $filas = $this->leerFilas();
        $filas[] = array_merge(
            ['id' => uniqid()],
            $datosValidados
        );

        $this->guardarFilas($filas);

        return redirect()->route('planificacion.index')
                         ->with('success', 'Fila añadida correctamente.');
    }

    /**
     * Muestra el formulario para editar una fila.
     *
     * @param string $id Identificador de la fila
     * @return \Illuminate\View\View Vista de edicion
     */
    public function edit(string $id)
    {
        if (!$this->verificarPermisoAdmin()) {
            return redirect()->route('planificacion.index')
                ->with('warning', 'Solo administradores pueden modificar la planificacion.');
        }

        $filas = $this->leerFilas();
        $fila = collect($filas)->firstWhere('id', $id);

        if (!$fila) {
            return redirect()->route('planificacion.index')
                ->with('warning', 'Fila no encontrada.');
        }

        return view('planificacion.edit', compact('fila'));
    }

    /**
     * Actualiza una fila de planificacion existente.
     *
     * @param Request $request Nuevos datos
     * @param string $id Identificador de la fila
     * @return \Illuminate\Http\RedirectResponse Redireccion con mensaje
     */
    public function update(Request $request, string $id)
    {
        if (!$this->verificarPermisoAdmin()) {
            return redirect()->route('planificacion.index')
                ->with('warning', 'Solo administradores pueden modificar la planificacion.');
        }

        $datosValidados = $request->validate([
            'NumeroCisterna'         => 'required|integer',
            'Destino'                => 'nullable|string|max:255',
            'FechaConsumo'           => 'nullable|date',
            'FechaFabricacionHuelva' => 'nullable|date',
            'HoraEstimadaConsumoL1'  => 'nullable|date_format:H:i',
            'HoraEstimadaConsumoL2'  => 'nullable|date_format:H:i',
        ]);

        $filas = $this->leerFilas();
        $filas = array_map(function ($fila) use ($id, $datosValidados) {
            if ($fila['id'] === $id) {
                return array_merge(['id' => $id], $datosValidados);
            }
            return $fila;
        }, $filas);

        $this->guardarFilas($filas);

        return redirect()->route('planificacion.index')
                         ->with('success', 'Fila actualizada correctamente.');
    }

    /**
     * Elimina una fila de planificacion.
     *
     * @param string $id Identificador de la fila
     * @return \Illuminate\Http\RedirectResponse Redireccion con mensaje
     */
    public function destroy(string $id)
    {
        if (!$this->verificarPermisoAdmin()) {
            return redirect()->route('planificacion.index')
                ->with('warning', 'Solo administradores pueden modificar la planificacion.');
        }

        $filas = $this->leerFilas();
        $filas = array_values(array_filter($filas, fn ($f) => $f['id'] !== $id));

        $this->guardarFilas($filas);

        return redirect()->route('planificacion.index')
                         ->with('success', 'Fila eliminada correctamente.');
    }

    /**
     * Elimina todas las filas de planificacion.
     *
     * @return \Illuminate\Http\RedirectResponse Redireccion con mensaje
     */
    public function clear()
    {
        if (!$this->verificarPermisoAdmin()) {
            return redirect()->route('planificacion.index')
                ->with('warning', 'Solo administradores pueden modificar la planificacion.');
        }
        $this->guardarFilas([]);

        return redirect()->route('planificacion.index')
                         ->with('success', 'Planificacion limpiada correctamente.');
    }

    // ==================== EXPORTAR ====================

    /**
     * Genera y descarga el Excel de planificacion.
     *
     * @return \Symfony\Component\HttpFoundation\BinaryFileResponse|\Illuminate\Http\RedirectResponse
     */
    public function exportar()
    {
        $filas = $this->leerFilas();

        if (empty($filas)) {
            return redirect()->route('planificacion.index')
                             ->with('warning', 'No hay filas para exportar.');
        }

        $rutaTemporal = $this->generarExcelPlanificacion($filas);

        return response()->download($rutaTemporal)->deleteFileAfterSend(true);
    }

    /**
     * Genera el archivo Excel de planificacion.
     *
     * @param array $filas Filas de planificacion
     * @return string Ruta del archivo temporal
     */
    private function generarExcelPlanificacion(array $filas): string
    {
        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Planificacion');

        // Escribir cabeceras
        foreach (self::COLUMNAS_EXCEL as $columna => $titulo) {
            $sheet->setCellValue($columna . '1', $titulo);
        }

        // Aplicar estilo a cabecera
        $estiloCabecera = $sheet->getStyle('A1:F1');
        $estiloCabecera->getFont()->setBold(true);
        $estiloCabecera->getFont()->getColor()->setARGB(self::COLOR_TEXTO_BLANCO);
        $estiloCabecera->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID);
        $estiloCabecera->getFill()->getStartColor()->setARGB(self::COLOR_CABECERA_FONDO);
        $estiloCabecera->getAlignment()->setHorizontal(
            \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER
        );

        // Escribir datos
        $fila = 2;
        foreach ($filas as $datos) {
            $sheet->setCellValue('A' . $fila, str_pad((string) $datos['NumeroCisterna'], 4, '0', STR_PAD_LEFT));
            $sheet->setCellValue('B' . $fila, $datos['Destino'] ?? '');
            $sheet->setCellValue('C' . $fila, $datos['FechaConsumo'] ?? '');
            $sheet->setCellValue('D' . $fila, $datos['FechaFabricacionHuelva'] ?? '');
            $sheet->setCellValue('E' . $fila, $datos['HoraEstimadaConsumoL1'] ?? '');
            $sheet->setCellValue('F' . $fila, $datos['HoraEstimadaConsumoL2'] ?? '');
            $fila++;
        }

        // Ajustar anchos
        foreach (array_keys(self::COLUMNAS_EXCEL) as $columna) {
            $sheet->getColumnDimension($columna)->setAutoSize(true);
        }

        // Guardar archivo
        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
        $nombreArchivo = 'planificacion_' . now()->format(self::FORMATO_FECHA_ARCHIVO) . '.xlsx';
        $rutaTemporal = storage_path('app/private/temp/' . $nombreArchivo);

        $directorioTemporal = dirname($rutaTemporal);
        if (!file_exists($directorioTemporal)) {
            mkdir($directorioTemporal, 0755, true);
        }

        $writer->save($rutaTemporal);

        return $rutaTemporal;
    }

    // ==================== PERMISOS ====================

    /**
     * Verifica que el usuario tenga permisos de administrador.
     *
     */
    private function verificarPermisoAdmin(): bool
    {
        return (bool) optional(auth()->user())->isAdmin();
    }
}
