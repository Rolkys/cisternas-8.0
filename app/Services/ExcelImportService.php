<?php

/**
 * Servicio de importacion de datos de cisternas desde archivos Excel.
 * 
 * Este servicio proporciona funcionalidades para:
 * - Previsualizar datos de cisternas antes de importarlos
 * - Importar cisternas validando duplicados
 * - Extraer y transformar datos de hojas Excel con formato especifico
 */

namespace App\Services;

use App\Models\Cisterna;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Shared\Date;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class ExcelImportService
{
    /**
     * Coordenadas de celdas para datos principales de la cisterna.
     * Define la estructura del Excel de entrada.
     */
    private const CELDA_OF = 'M3';
    private const CELDA_NUMERO_CISTERNA = 'M2';
    private const CELDA_CONDUCTOR = 'H16';
    private const CELDA_TELEFONO = 'H17';
    private const CELDA_ORIGEN = 'M9';
    private const CELDA_DESTINO = 'M10';
    private const CELDA_MATRICULA = 'M5';
    private const CELDA_MATRICULA_CISTERNA = 'M6';
    private const CELDA_TRANSPORTE = 'M7';
    private const CELDA_FECHA_FABRICACION = 'M1';
    
    /**
     * Coordenadas de celdas para fechas y horas de transporte.
     */
    private const CELDA_FECHA_SALIDA = 'D16';
    private const CELDA_HORA_SALIDA = 'D17';
    private const CELDA_FECHA_LLEGADA = 'J16';
    private const CELDA_HORA_LLEGADA = 'J17';
    
    /**
     * Coordenadas de celdas para datos de observaciones.
     */
    private const CELDA_CONCEPTO = 'C14';
    private const CELDA_BRIX = 'H14';
    private const CELDA_KILOS = 'L14';
    private const CELDA_PRECINTOS = 'D15';
    private const CELDA_TARA = 'J15';
    
    /**
     * Campos clave para validar si una hoja contiene datos utiles.
     */
    private const CAMPOS_REQUERIDOS = ['OF', 'NumeroCisterna', 'Conductor'];

    /**
     * Genera una previsualizacion de filas extraidas desde un Excel.
     * 
     * Permite revisar los datos antes de importarlos, opcionalmente
     * ocultando las cisternas que ya existen en la base de datos.
     *
     * @param string $filePath Ruta al archivo Excel
     * @param bool $skipExisting Si es true, omite cisternas ya existentes
     * @return array Listado de datos extraidos con metadatos de hoja
     */
    public function preview(string $filePath, bool $skipExisting = true): array
    {
        return $this->procesarExcel($filePath, function ($data, $name) use ($skipExisting): ?array {
            // Omitir cisternas existentes si se solicita
            if ($skipExisting && $this->existeCisterna($data)) {
                return null;
            }

            return array_merge($data, [
                '_hoja' => $name,
                '_error' => null,
            ]);
        });
    }

    /**
     * Importa filas de un Excel aplicando validaciones de integridad.
     * 
     * Verifica duplicados antes de crear y registra errores por hoja.
     *
     * @param string $filePath Ruta al archivo Excel
     * @return array Resultado con contador de importados y lista de errores
     */
    public function import(string $filePath): array
    {
        $imported = 0;
        $errors = [];

        $this->procesarExcel($filePath, function ($data, $name) use (&$imported, &$errors): ?array {
            // Validar duplicados: no se importa si ya existe
            if ($this->existeCisterna($data)) {
                $errors[] = sprintf(
                    'Hoja %s: Ya existe cisterna con OF %s y N %s - omitida.',
                    $name,
                    $data['OF'],
                    $data['NumeroCisterna']
                );
                return null;
            }

            try {
                Cisterna::create($data);
                $imported++;
            } catch (\Throwable $e) {
                $errors[] = sprintf('Hoja %s: Error al crear cisterna - %s', $name, $e->getMessage());
            }

            return null;
        });

        return compact('imported', 'errors');
    }

    /**
     * Metodo base para procesar todas las hojas de un Excel.
     * 
     * Itera sobre cada hoja, extrae los datos y aplica un callback
     * de procesamiento personalizado por hoja.
     *
     * @param string $filePath Ruta al archivo Excel
     * @param callable $callback Funcion que recibe ($data, $nombreHoja) y retorna array|null
     * @return array Resultados acumulados del callback
     */
    private function procesarExcel(string $filePath, callable $callback): array
    {
        $spreadsheet = IOFactory::load($filePath);
        $resultados = [];

        foreach ($spreadsheet->getWorksheetIterator() as $worksheet) {
            $nombreHoja = trim($worksheet->getTitle());

            try {
                $data = $this->extractFromSheet($worksheet);

                // Ignorar hojas vacias sin datos relevantes
                if ($this->datosRequeridosVacios($data)) {
                    continue;
                }

                $resultado = $callback($data, $nombreHoja);
                if ($resultado !== null) {
                    $resultados[] = $resultado;
                }
            } catch (\Throwable $e) {
                $resultados[] = [
                    '_hoja' => $nombreHoja,
                    '_error' => $e->getMessage(),
                ];
            }
        }

        return $resultados;
    }

    /**
     * Verifica si una cisterna con los mismos OF y NumeroCisterna ya existe.
     *
     * @param array $data Datos extraidos de la hoja
     * @return bool True si ya existe en la base de datos
     */
    private function existeCisterna(array $data): bool
    {
        return Cisterna::where('OF', $data['OF'])
            ->where('NumeroCisterna', $data['NumeroCisterna'])
            ->exists();
    }

    /**
     * Comprueba si los campos requeridos estan todos vacios.
     *
     * @param array $data Datos extraidos de la hoja
     * @return bool True si todos los campos clave estan vacios
     */
    private function datosRequeridosVacios(array $data): bool
    {
        foreach (self::CAMPOS_REQUERIDOS as $campo) {
            if (!empty($data[$campo])) {
                return false;
            }
        }
        return true;
    }

    /**
     * Extrae y transforma los datos de una hoja concreta del Excel.
     * 
     * Lee las celdas definidas en las constantes de clase y las convierte
     * al formato de base de datos requerido por el modelo Cisterna.
     *
     * @param Worksheet $ws Hoja de Excel a procesar
     * @return array Datos estructurados listos para crear o previsualizar
     */
    private function extractFromSheet(Worksheet $ws): array
    {
        $observaciones = $this->buildObservaciones($ws);
        $fechaConsumo = $this->parseDate($ws->getCell(self::CELDA_FECHA_LLEGADA)->getValue());

        return [
            // Identificacion principal
            'OF' => (int) $this->cellValue($ws, self::CELDA_OF),
            'NumeroCisterna' => (int) $this->cellValue($ws, self::CELDA_NUMERO_CISTERNA),
            
            // Datos del conductor
            'Conductor' => $this->cellValue($ws, self::CELDA_CONDUCTOR),
            'Telefono' => $this->cellValue($ws, self::CELDA_TELEFONO),
            
            // Ubicaciones del transporte
            'Origen' => $this->cellValue($ws, self::CELDA_ORIGEN),
            'Destino' => $this->cellValue($ws, self::CELDA_DESTINO),
            
            // Matriculas e informacion del vehiculo
            'Matricula' => $this->cellValue($ws, self::CELDA_MATRICULA),
            'MatriculaCisterna' => $this->cellValue($ws, self::CELDA_MATRICULA_CISTERNA),
            'Transporte' => $this->cellValue($ws, self::CELDA_TRANSPORTE),
            
            // Fechas relacionadas con el transporte
            'FechaFabricacionHuelva' => $this->parseDate($ws->getCell(self::CELDA_FECHA_FABRICACION)->getValue()),
            'HoraSalida' => $this->parseDateTime(
                $ws->getCell(self::CELDA_FECHA_SALIDA)->getValue(),
                $ws->getCell(self::CELDA_HORA_SALIDA)->getValue()
            ),
            'FechaEntradaMG' => $fechaConsumo,
            'HoraLlegadaEstimada' => $this->parseDateTime(
                $ws->getCell(self::CELDA_FECHA_LLEGADA)->getValue(),
                $ws->getCell(self::CELDA_HORA_LLEGADA)->getValue()
            ),
            'FechaConsumoMG' => $fechaConsumo,
            
            // Campos de consumo inicializados en null (se completan posteriormente)
            'HoraEstimadaConsumoL1' => null,
            'HoraEstimadaConsumoL2' => null,
            'HoraRealConsumoL1' => null,
            'HoraRealConsumoL2' => null,
            
            // Certificaciones inicializadas en false
            'GlobalGAP' => false,
            'FDA' => false,
            
            // Observaciones extraidas de multiples celdas
            'Observaciones' => $observaciones ?: null,
            'Incidencias' => null,
        ];
    }

    /**
     * Obtiene el valor limpio de una celda del Excel.
     * 
     * Aplica trim al valor calculado para eliminar espacios en blanco.
     *
     * @param Worksheet $ws Hoja de trabajo
     * @param string $coord Coordenada de la celda (ej: 'M3')
     * @return string Valor limpio de la celda
     */
    private function cellValue(Worksheet $ws, string $coord): string
    {
        $raw = $ws->getCell($coord)->getCalculatedValue();
        return trim((string) ($raw ?? ''));
    }

    /**
     * Construye el texto de observaciones a partir de varias celdas.
     * 
     * Combina concepto, BRIX, kilos, precintos y tara en una sola cadena
     * separada por pipes, omitiendo campos vacios.
     *
     * @param Worksheet $ws Hoja de trabajo
     * @return string|null Texto de observaciones o null si no hay datos
     */
    private function buildObservaciones(Worksheet $ws): ?string
    {
        $etiquetas = [
            'Concepto' => $this->cellValue($ws, self::CELDA_CONCEPTO),
            'BRIX' => $this->cellValue($ws, self::CELDA_BRIX),
            'Kilos' => $this->cellValue($ws, self::CELDA_KILOS),
            'Precintos' => $this->cellValue($ws, self::CELDA_PRECINTOS),
            'Tara' => $this->cellValue($ws, self::CELDA_TARA),
        ];

        $partes = [];
        foreach ($etiquetas as $etiqueta => $valor) {
            if ($valor !== '') {
                $partes[] = "{$etiqueta}: {$valor}";
            }
        }

        return $partes === [] ? null : implode(' | ', $partes);
    }

    /**
     * Convierte un valor de celda en fecha compatible con la base de datos.
     * 
     * Soporta:
     * - Numeros de serie Excel (formato de fecha de Excel)
     * - Objetos DateTime ya instanciados
     * - Cadenas de texto parseables
     *
     * @param mixed $value Valor de celda a convertir
     * @return string|null Fecha en formato 'Y-m-d H:i:s' o null si es invalida
     */
    private function parseDate($value): ?string
    {
        if ($value === null || $value === '') {
            return null;
        }

        // Numeros de serie Excel
        if (is_numeric($value)) {
            return Date::excelToDateTimeObject((float) $value)->format('Y-m-d H:i:s');
        }

        // Objetos DateTime
        if ($value instanceof \DateTime) {
            return $value->format('Y-m-d H:i:s');
        }

        // Intentar parsear como cadena
        try {
            return (new \DateTime((string) $value))->format('Y-m-d H:i:s');
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * Combina fecha y hora de celdas Excel en un datetime normalizado.
     * 
     * Toma la fecha base y ajusta la hora si se proporciona un valor de tiempo.
     * Soporta ambos formatos: numeros de serie Excel y objetos DateTime.
     *
     * @param mixed $dateValue Valor de la celda con la fecha
     * @param mixed $timeValue Valor de la celda con la hora (opcional)
     * @return string|null Datetime en formato 'Y-m-d H:i:s' o null si la fecha es invalida
     */
    private function parseDateTime($dateValue, $timeValue): ?string
    {
        if ($dateValue === null || $dateValue === '') {
            return null;
        }

        // Convertir valor de fecha a DateTime
        if (is_numeric($dateValue)) {
            $date = Date::excelToDateTimeObject((float) $dateValue);
        } elseif ($dateValue instanceof \DateTime) {
            $date = clone $dateValue;
        } else {
            try {
                $date = new \DateTime((string) $dateValue);
            } catch (\Exception $e) {
                return null;
            }
        }

        // Aplicar hora si existe
        if ($timeValue !== null && $timeValue !== '') {
            $hora = is_numeric($timeValue)
                ? Date::excelToDateTimeObject((float) $timeValue)
                : ($timeValue instanceof \DateTime ? $timeValue : null);

            if ($hora !== null) {
                $date->setTime((int) $hora->format('H'), (int) $hora->format('i'));
            }
        }

        return $date->format('Y-m-d H:i:s');
    }
}

