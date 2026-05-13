<?php

/**
 * Modelo Cisterna - Gestion de transporte de cisternas de zumo.
 * 
 * Representa una cisterna de transporte con toda su informacion:
 * - Identificacion (OF, NumeroCisterna)
 * - Datos del conductor y transporte
 * - Fechas de fabricacion, salida, llegada y consumo
 * - Tiempos estimados y reales de consumo
 * - Certificaciones (GlobalGAP, FDA)
 * - Observaciones e incidencias
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Cisterna extends Model
{
    /**
     * Nombre de la clave primaria.
     */
    protected $primaryKey = 'IdCisterna';

    /**
     * Formato de fechas para SQL Server.
     */
    protected $dateFormat = 'Ymd H:i:s';

    /**
     * Campos asignables en asignacion masiva.
     * 
     * Organizados por categoria:
     * - Identificacion
     * - Conductor y contacto
     * - Ubicaciones
     * - Vehiculo
     * - Fechas de transporte
     * - Horas de consumo
     * - Certificaciones
     * - Notas
     */
    protected $fillable = [
        // Identificacion
        'OF',
        'NumeroCisterna',
        
        // Conductor y contacto
        'Conductor',
        'Telefono',
        
        // Ubicaciones
        'Origen',
        'Destino',
        
        // Vehiculo
        'Matricula',
        'MatriculaCisterna',
        'Transporte',
        
        // Fechas de transporte
        'FechaFabricacionHuelva',
        'HoraSalida',
        'FechaEntradaMG',
        'HoraLlegadaEstimada',
        'FechaConsumoMG',
        
        // Horas de consumo
        'HoraEstimadaConsumoL1',
        'HoraEstimadaConsumoL2',
        'HoraRealConsumoL1',
        'HoraRealConsumoL2',
        
        // Certificaciones
        'GlobalGAP',
        'FDA',
        
        // Notas
        'Observaciones',
        'Incidencias',
    ];

    /**
     * Conversiones automaticas de tipos de datos.
     * 
     * @return array<string, string> Mapeo de campos a tipos
     */
    protected function casts(): array
    {
        return [
            // Fechas de transporte y fabricacion
            'FechaFabricacionHuelva' => 'datetime',
            'HoraSalida'             => 'datetime',
            'FechaEntradaMG'         => 'datetime',
            'HoraLlegadaEstimada'    => 'datetime',
            'FechaConsumoMG'         => 'datetime',
            
            // Horas estimadas de consumo
            'HoraEstimadaConsumoL1'  => 'datetime',
            'HoraEstimadaConsumoL2'  => 'datetime',
            
            // Horas reales de consumo
            'HoraRealConsumoL1'      => 'datetime',
            'HoraRealConsumoL2'      => 'datetime',
            
            // Certificaciones booleanas
            'GlobalGAP'              => 'boolean',
            'FDA'                    => 'boolean',
        ];
    }

    /**
     * Accesor: Obtiene el numero de cisterna formateado con ceros.
     *
     * @return string Numero formateado (ej: 0042)
     */
    public function getNumeroFormateadoAttribute(): string
    {
        return str_pad((string) $this->NumeroCisterna, 4, '0', STR_PAD_LEFT);
    }

    /**
     * Accesor: Determina el estado visual de la cisterna.
     *
     * Estados posibles:
     * - 'incidencia': Tiene incidencias registradas
     * - 'consumida': Ya tiene hora real de consumo
     * - 'hoy': Fecha de consumo es hoy
     * - 'futura': Fecha de consumo es futura
     * - 'pendiente': Sin fecha de consumo asignada
     *
     * @return string Estado actual de la cisterna
     */
    public function getEstadoAttribute(): string
    {
        if (!empty($this->Incidencias)) {
            return 'incidencia';
        }

        if ($this->HoraRealConsumoL1 || $this->HoraRealConsumoL2) {
            return 'consumida';
        }

        if ($this->FechaConsumoMG === null) {
            return 'pendiente';
        }

        if ($this->FechaConsumoMG->isToday()) {
            return 'hoy';
        }

        if ($this->FechaConsumoMG->isFuture()) {
            return 'futura';
        }

        return 'pasada';
    }

    /**
     * Scope: Filtra cisternas por estado de consumo.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeConsumidas($query)
    {
        return $query->where(function ($q) {
            $q->whereNotNull('HoraRealConsumoL1')
              ->orWhereNotNull('HoraRealConsumoL2');
        });
    }

    /**
     * Scope: Filtra cisternas pendientes de consumo.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopePendientes($query)
    {
        return $query->whereNull('HoraRealConsumoL1')
                     ->whereNull('HoraRealConsumoL2')
                     ->whereNull('Incidencias');
    }

    /**
     * Scope: Filtra cisternas programadas para hoy.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeHoy($query)
    {
        return $query->whereDate('FechaConsumoMG', today());
    }
}

