<?php

/**
 * Migración de la tabla Cisternas
 * 
 * Crea la estructura para almacenar información completa sobre las cisternas,
 * incluyendo datos logísticos, fechas de transporte y certificaciones.
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Ejecuta la migración creando la tabla de cisternas.
     */
    public function up(): void
    {
        Schema::create('cisternas', function (Blueprint $table) {
            // Identificador principal
            $table->id('IdCisterna')->comment('ID único de la cisterna');
            
            // Datos de orden y numeración
            $table->integer('OF')->comment('Número de orden de fabricación');
            $table->integer('NumeroCisterna')->comment('Número secuencial de cisterna');
            
            // Información logística
            $table->string('Origen')->nullable()->comment('Lugar de origen de la cisterna');
            $table->string('Destino')->nullable()->comment('Destino final de la cisterna');
            $table->string('Matricula')->nullable()->comment('Matrícula del vehículo tractor');
            $table->string('MatriculaCisterna')->nullable()->comment('Matrícula de la cisterna');
            
            // Datos del conductor y transporte
            $table->string('Conductor')->comment('Nombre completo del conductor');
            $table->string('Telefono')->nullable()->comment('Teléfono de contacto del conductor');
            $table->string('Transporte')->nullable()->comment('Empresa de transporte');
            
            // Fechas y horas del proceso - Fabricación en Huelva
            $table->dateTime('FechaFabricacionHuelva')->nullable()->comment('Fecha y hora de fabricación en Huelva');
            $table->dateTime('HoraSalida')->nullable()->comment('Hora de salida desde origen');
            
            // Fechas y horas del proceso - Entrada a Madrid
            $table->dateTime('FechaEntradaMG')->nullable()->comment('Fecha de entrada en Madrid/Guadalajara');
            $table->dateTime('HoraLlegadaEstimada')->nullable()->comment('Hora estimada de llegada');
            
            // Fechas y horas del proceso - Consumo en Madrid
            $table->dateTime('FechaConsumoMG')->nullable()->comment('Fecha de consumo en Madrid/Guadalajara');
            $table->dateTime('HoraEstimadaConsumoL1')->nullable()->comment('Hora estimada consumo línea 1');
            $table->dateTime('HoraEstimadaConsumoL2')->nullable()->comment('Hora estimada consumo línea 2');
            $table->dateTime('HoraRealConsumoL1')->nullable()->comment('Hora real consumo línea 1');
            $table->dateTime('HoraRealConsumoL2')->nullable()->comment('Hora real consumo línea 2');
            
            // Certificaciones y calidad
            $table->boolean('GlobalGAP')->nullable()->comment('Certificación GlobalGAP');
            $table->boolean('FDA')->nullable()->comment('Certificación FDA (Estados Unidos)');
            
            // Observaciones e incidencias
            $table->text('Observaciones')->nullable()->comment('Notas y observaciones adicionales');
            $table->text('Incidencias')->nullable()->comment('Registro de incidencias durante el transporte');
            
            // Timestamps automáticos
            $table->timestamps();
        });
    }

    /**
     * Revierte la migración eliminando la tabla de cisternas.
     */
    public function down(): void
    {
        Schema::dropIfExists('cisternas');
    }
};