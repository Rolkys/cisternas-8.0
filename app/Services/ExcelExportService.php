<?php

/**
 * Servicio de exportacion de cisternas a formato Excel.
 * 
 * Genera archivos Excel con los datos de cisternas incluyendo:
 * - Formato profesional con cabecera estilizada
 * - Colores de estado para cada fila (incidencias, consumidas, pendientes)
 * - Ajuste automatico de anchos de columna
 */

namespace App\Services;

use App\Models\Cisterna;
use Illuminate\Database\Eloquent\Collection;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Cell\DataType;

class ExcelExportService
{
    /**
     * Columnas del Excel con sus respectivas cabeceras.
     * Define la estructura del archivo de exportacion.
     */
    private const COLUMNAS = [
        'A' => 'OF',
        'B' => 'Nº Cisterna',
        'C' => 'Conductor',
        'D' => 'Teléfono',
        'E' => 'Origen',
        'F' => 'Destino',
        'G' => 'Matrícula Camión',
        'H' => 'Matrícula Cisterna',
        'I' => 'Transporte',
        'J' => 'Hora Salida',
        'K' => 'Fecha Entrada MG',
        'L' => 'Fecha Consumo MG',
        'M' => 'H. Est. Consumo L1',
        'N' => 'H. Est. Consumo L2',
        'O' => 'H. Real Consumo L1',
        'P' => 'H. Real Consumo L2',
        'Q' => 'GlobalGAP',
        'R' => 'FDA',
        'S' => 'Observaciones',
        'T' => 'Incidencias',
    ];

    /**
     * Colores ARGB para los diferentes estados de las cisternas.
     */
    private const COLOR_ROJO_INCIDENCIA = 'FFFF746C';
    private const COLOR_VERDE_CONSUMIDA = 'FFadebb3';
    private const COLOR_AZUL_HOY = 'FF90D5FF';
    private const COLOR_AMARILLO_FUTURO = 'FFFFEE8C';
    private const COLOR_CABECERA_FONDO = 'FF0F2130';
    private const COLOR_BLANCO_TEXTO = 'FFFFFFFF';

    /**
     * Formato de fecha para el nombre del archivo.
     */
    private const FORMATO_FECHA_ARCHIVO = 'Y-m-d_H-i';

    /**
     * Exporta una coleccion de cisternas a un archivo Excel.
     * 
     * Aplica estilos segun el estado de cada cisterna:
     * - Rojo: Tiene incidencias
     * - Verde: Ya ha sido consumida
     * - Azul: Consumo programado para hoy
     * - Amarillo: Consumo programado para fecha futura
     *
     * @param Collection<int, Cisterna> $cisternas Coleccion de cisternas a exportar
     * @return string Ruta del archivo temporal generado
     */
    public function export(Collection $cisternas): string
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Cisternas');

        $this->escribirCabecera($sheet);
        $this->escribirDatos($sheet, $cisternas);
        $this->ajustarAnchosColumnas($sheet);

        return $this->generarArchivo($spreadsheet);
    }

    /**
     * Escribe la fila de cabecera con estilo profesional.
     *
     * @param \PhpOffice\PhpSpreadsheet\Worksheet\Worksheet $sheet Hoja activa
     */
    private function escribirCabecera($sheet): void
    {
        foreach (self::COLUMNAS as $columna => $titulo) {
            $sheet->setCellValue($columna . '1', $titulo);
        }

        $estiloCabecera = $sheet->getStyle('A1:T1');
        $estiloCabecera->getFont()->setBold(true);
        $estiloCabecera->getFont()->getColor()->setARGB(self::COLOR_BLANCO_TEXTO);
        $estiloCabecera->getFill()->setFillType(Fill::FILL_SOLID);
        $estiloCabecera->getFill()->getStartColor()->setARGB(self::COLOR_CABECERA_FONDO);
        $estiloCabecera->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
    }

    /**
     * Escribe los datos de las cisternas y aplica colores segun estado.
     *
     * @param \PhpOffice\PhpSpreadsheet\Worksheet\Worksheet $sheet Hoja activa
     * @param Collection<int, Cisterna> $cisternas Coleccion de cisternas
     */
    private function escribirDatos($sheet, Collection $cisternas): void
    {
        $fila = 2;
        $hoy = now()->startOfDay();

        foreach ($cisternas as $cisterna) {
            $this->escribirFilaCisterna($sheet, $fila, $cisterna);
            $this->aplicarColorFila($sheet, $fila, $cisterna, $hoy);
            $fila++;
        }
    }

    /**
     * Escribe los datos de una cisterna en una fila especifica.
     *
     * @param \PhpOffice\PhpSpreadsheet\Worksheet\Worksheet $sheet Hoja activa
     * @param int $fila Numero de fila
     * @param Cisterna $cisterna Cisterna a escribir
     */
    private function escribirFilaCisterna($sheet, int $fila, Cisterna $cisterna): void
    {
        $sheet->setCellValue("A{$fila}", $cisterna->OF);
        $sheet->setCellValue("B{$fila}", str_pad((string) $cisterna->NumeroCisterna, 4, '0', STR_PAD_LEFT));
        $this->setSafeTextCell($sheet, "C{$fila}", $cisterna->Conductor);
        $this->setSafeTextCell($sheet, "D{$fila}", $cisterna->Telefono);
        $this->setSafeTextCell($sheet, "E{$fila}", $cisterna->Origen);
        $this->setSafeTextCell($sheet, "F{$fila}", $cisterna->Destino);
        $this->setSafeTextCell($sheet, "G{$fila}", $cisterna->Matricula);
        $this->setSafeTextCell($sheet, "H{$fila}", $cisterna->MatriculaCisterna);
        $this->setSafeTextCell($sheet, "I{$fila}", $cisterna->Transporte);
        $sheet->setCellValue("J{$fila}", $cisterna->HoraSalida?->format('d/m/Y H:i'));
        $sheet->setCellValue("K{$fila}", $cisterna->FechaEntradaMG?->format('d/m/Y H:i'));
        $sheet->setCellValue("L{$fila}", $cisterna->FechaConsumoMG?->format('d/m/Y'));
        $sheet->setCellValue("M{$fila}", $cisterna->HoraEstimadaConsumoL1?->format('H:i'));
        $sheet->setCellValue("N{$fila}", $cisterna->HoraEstimadaConsumoL2?->format('H:i'));
        $sheet->setCellValue("O{$fila}", $cisterna->HoraRealConsumoL1?->format('H:i'));
        $sheet->setCellValue("P{$fila}", $cisterna->HoraRealConsumoL2?->format('H:i'));
        $sheet->setCellValue("Q{$fila}", $this->formatearBooleano($cisterna->GlobalGAP));
        $sheet->setCellValue("R{$fila}", $this->formatearBooleano($cisterna->FDA));
        $this->setSafeTextCell($sheet, "S{$fila}", $cisterna->Observaciones);
        $this->setSafeTextCell($sheet, "T{$fila}", $cisterna->Incidencias);
    }

    private function setSafeTextCell($sheet, string $cell, $value): void
    {
        $text = (string) ($value ?? '');

        if ($text !== '' && preg_match('/^[=\+\-@\t\r]/', $text)) {
            $text = "'" . $text;
        }

        $sheet->setCellValueExplicit($cell, $text, DataType::TYPE_STRING);
    }

    /**
     * Formatea un valor booleano para mostrar en Excel.
     *
     * @param bool|null $valor Valor booleano o null
     * @return string Texto formateado
     */
    private function formatearBooleano(?bool $valor): string
    {
        if ($valor === null) {
            return '—';
        }
        return $valor ? 'Sí' : 'No';
    }

    /**
     * Aplica color de fondo a la fila segun el estado de la cisterna.
     *
     * @param \PhpOffice\PhpSpreadsheet\Worksheet\Worksheet $sheet Hoja activa
     * @param int $fila Numero de fila
     * @param Cisterna $cisterna Cisterna a evaluar
     * @param \Carbon\Carbon $hoy Fecha actual
     */
    private function aplicarColorFila($sheet, int $fila, Cisterna $cisterna, $hoy): void
    {
        $color = $this->determinarColorFila($cisterna, $hoy);

        if ($color === null) {
            return;
        }

        $sheet->getStyle("A{$fila}:T{$fila}")->applyFromArray([
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['argb' => $color],
            ],
        ]);
    }

    /**
     * Determina el color correspondiente al estado de la cisterna.
     *
     * @param Cisterna $cisterna Cisterna a evaluar
     * @param \Carbon\Carbon $hoy Fecha actual
     * @return string|null Codigo de color ARGB o null si no aplica
     */
    private function determinarColorFila(Cisterna $cisterna, $hoy): ?string
    {
        // Prioridad 1: Incidencias (rojo)
        if (!empty($cisterna->Incidencias)) {
            return self::COLOR_ROJO_INCIDENCIA;
        }

        // Prioridad 2: Ya consumida (verde)
        if ($cisterna->HoraRealConsumoL1 || $cisterna->HoraRealConsumoL2) {
            return self::COLOR_VERDE_CONSUMIDA;
        }

        if ($cisterna->FechaConsumoMG === null) {
            return null;
        }

        // Prioridad 3: Consumo hoy (azul)
        if ($cisterna->FechaConsumoMG->isSameDay($hoy)) {
            return self::COLOR_AZUL_HOY;
        }

        // Prioridad 4: Consumo futuro (amarillo)
        if ($cisterna->FechaConsumoMG->isAfter($hoy)) {
            return self::COLOR_AMARILLO_FUTURO;
        }

        return null;
    }

    /**
     * Ajusta el ancho de todas las columnas automaticamente.
     *
     * @param \PhpOffice\PhpSpreadsheet\Worksheet\Worksheet $sheet Hoja activa
     */
    private function ajustarAnchosColumnas($sheet): void
    {
        foreach (range('A', 'T') as $columna) {
            $sheet->getColumnDimension($columna)->setAutoSize(true);
        }
    }

    /**
     * Genera el archivo Excel y lo guarda en ruta temporal.
     *
     * @param Spreadsheet $spreadsheet Documento Excel
     * @return string Ruta del archivo generado
     */
    private function generarArchivo(Spreadsheet $spreadsheet): string
    {
        $writer = new Xlsx($spreadsheet);
        $nombreArchivo = 'cisternas_' . now()->format(self::FORMATO_FECHA_ARCHIVO) . '.xlsx';
        $rutaTemporal = storage_path('app/private/temp/' . $nombreArchivo);

        // Crear directorio temporal si no existe
        $directorioTemporal = dirname($rutaTemporal);
        if (!file_exists($directorioTemporal)) {
            mkdir($directorioTemporal, 0755, true);
        }

        $writer->save($rutaTemporal);

        return $rutaTemporal;
    }
}
