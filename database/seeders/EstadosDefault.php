<?php

namespace Database\Seeders;

use App\Models\DetalleEstadoProduccion;
use App\Models\EstadoProduccion;
use App\Models\ProcesoEstadoProduccion;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class EstadosDefault extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // PRIMERO CREAREMOS LOS ESTADOS DE PRODUCCIÓN-----------------------
        EstadoProduccion::create([
            'nombre' => 'Área de bodega',
            'users_id' => 3,
            'orden' => 1,
        ]);
        EstadoProduccion::create([
            'nombre' => 'Área de impresión',
            'users_id' => 4,
            'orden' => 2,
        ]);
        EstadoProduccion::create([
            'nombre' => 'Área de corte',
            'users_id' => 3,
            'orden' => 3,
        ]);
        EstadoProduccion::create([
            'nombre' => 'Área de troquelado',
            'users_id' => 3,
            'orden' => 4,
        ]);
        EstadoProduccion::create([
            'nombre' => 'Área de producción',
            'users_id' => 5,
            'orden' => 5,
        ]);
        EstadoProduccion::create([
            'nombre' => 'Área de colocación de cinta',
            'users_id' => 6,
            'orden' => 6,
        ]);
        EstadoProduccion::create([
            'nombre' => 'Área de revisión y calidad',
            'users_id' => 7,
            'orden' => 7,
        ]);
        EstadoProduccion::create([
            'nombre' => 'Área de empaque',
            'users_id' => 7,
            'orden' => 7,
        ]);


        // SEGUNDO CREAMOS SUB ESTADOS
        ProcesoEstadoProduccion::create([
            'nombre' => 'PAPEL ENTREGADO EN IMPRE',
            'estado_produccions_id' => 1,
        ]);
        ProcesoEstadoProduccion::create([
            'nombre' => 'PROCESO CORTE',
            'estado_produccions_id' => 1,
        ]);
        ProcesoEstadoProduccion::create([
            'nombre' => 'ATRASO DE IMPRESION',
            'estado_produccions_id' => 1,
        ]);
        ProcesoEstadoProduccion::create([
            'nombre' => 'MATERIA PRIMA NO EXISTENTE',
            'estado_produccions_id' => 1,
        ]);
        ProcesoEstadoProduccion::create([
            'nombre' => 'FALTA DE INFORME',
            'estado_produccions_id' => 1,
        ]);
        ProcesoEstadoProduccion::create([
            'nombre' => 'PAPEL PRIORIZADO',
            'estado_produccions_id' => 1,
        ]);
        ProcesoEstadoProduccion::create([
            'nombre' => 'PAPEL INCOMPLETO',
            'estado_produccions_id' => 1,
        ]);
        ProcesoEstadoProduccion::create([
            'nombre' => 'SIN VERIFICAR',
            'estado_produccions_id' => 1,
        ]);
        ProcesoEstadoProduccion::create([
            'nombre' => 'EN AREA DE CORTE',
            'estado_produccions_id' => 1,
        ]);
        ProcesoEstadoProduccion::create([
            'nombre' => 'FINALIZADO/REVISADO',
            'estado_produccions_id' => 1,
        ]);

        //-----------------
        ProcesoEstadoProduccion::create([
            'nombre' => 'FINALIZADO',
            'estado_produccions_id' => 2,
        ]);
        ProcesoEstadoProduccion::create([
            'nombre' => 'FALTA DE LOGO',
            'estado_produccions_id' => 2,
        ]);
        ProcesoEstadoProduccion::create([
            'nombre' => 'IMPRIMIR EN LA T3170X',
            'estado_produccions_id' => 2,
        ]);
        ProcesoEstadoProduccion::create([
            'nombre' => 'EN PROCESO',
            'estado_produccions_id' => 2,
        ]);
        ProcesoEstadoProduccion::create([
            'nombre' => 'SIN VERIFICAR',
            'estado_produccions_id' => 2,
        ]);
        ProcesoEstadoProduccion::create([
            'nombre' => 'ATRASO POR CAJA',
            'estado_produccions_id' => 2,
        ]);
        ProcesoEstadoProduccion::create([
            'nombre' => 'IMPRESION ESTAMPADO',
            'estado_produccions_id' => 2,
        ]);
        ProcesoEstadoProduccion::create([
            'nombre' => 'FALTA DE PLANTILLA',
            'estado_produccions_id' => 2,
        ]);
        ProcesoEstadoProduccion::create([
            'nombre' => 'ATRASO DE PAPEL',
            'estado_produccions_id' => 2,
        ]);
        ProcesoEstadoProduccion::create([
            'nombre' => 'FINALIZADO/REVISADO',
            'estado_produccions_id' => 2,
        ]);
        //----------------

        ProcesoEstadoProduccion::create([
            'nombre' => 'TAMAÑO FALTANTE',
            'estado_produccions_id' => 3,
        ]);
        ProcesoEstadoProduccion::create([
            'nombre' => 'PROCESO DE CORTE',
            'estado_produccions_id' => 3,
        ]);
        ProcesoEstadoProduccion::create([
            'nombre' => 'LISTO PARA PRODUCCION',
            'estado_produccions_id' => 3,
        ]);
        ProcesoEstadoProduccion::create([
            'nombre' => 'EN AREA DE IMPRESIÓN',
            'estado_produccions_id' => 3,
        ]);
        ProcesoEstadoProduccion::create([
            'nombre' => 'FALTA DE IMPRESIÓN',
            'estado_produccions_id' => 3,
        ]);
        ProcesoEstadoProduccion::create([
            'nombre' => 'SIN VERIFICAR',
            'estado_produccions_id' => 3,
        ]);
        ProcesoEstadoProduccion::create([
            'nombre' => 'FINALIZADO/REVISADO',
            'estado_produccions_id' => 3,
        ]);
        //------------------------
        ProcesoEstadoProduccion::create([
            'nombre' => 'FALTA DE IMPRESIÓN',
            'estado_produccions_id' => 4,
        ]);
        ProcesoEstadoProduccion::create([
            'nombre' => 'FALTA DE CORTE',
            'estado_produccions_id' => 4,
        ]);
        ProcesoEstadoProduccion::create([
            'nombre' => 'INCOMPLETAS',
            'estado_produccions_id' => 4,
        ]);
        ProcesoEstadoProduccion::create([
            'nombre' => 'FALTA DE TROQUEL',
            'estado_produccions_id' => 4,
        ]);
        ProcesoEstadoProduccion::create([
            'nombre' => 'MAL IMPRESAS',
            'estado_produccions_id' => 4,
        ]);
        ProcesoEstadoProduccion::create([
            'nombre' => 'ENVIADAS',
            'estado_produccions_id' => 4,
        ]);
        ProcesoEstadoProduccion::create([
            'nombre' => 'DESHECHADAS',
            'estado_produccions_id' => 4,
        ]);
        ProcesoEstadoProduccion::create([
            'nombre' => 'EN PROCESO',
            'estado_produccions_id' => 4,
        ]);
        ProcesoEstadoProduccion::create([
            'nombre' => 'FINALIZADO/REVISADO',
            'estado_produccions_id' => 4,
        ]);

        //--------------------
        ProcesoEstadoProduccion::create([
            'nombre' => 'ARMANDO BOLSAS',
            'estado_produccions_id' => 5,
        ]);
        ProcesoEstadoProduccion::create([
            'nombre' => 'SIN VERIFICAR',
            'estado_produccions_id' => 5,
        ]);
        ProcesoEstadoProduccion::create([
            'nombre' => 'BOLSAS FINALIZADAS',
            'estado_produccions_id' => 5,
        ]);
        ProcesoEstadoProduccion::create([
            'nombre' => 'BOLSAS FINALIZADAS EXISTENTES',
            'estado_produccions_id' => 5,
        ]);
        ProcesoEstadoProduccion::create([
            'nombre' => 'BOLSAS SIN  CINTA',
            'estado_produccions_id' => 5,
        ]);
        ProcesoEstadoProduccion::create([
            'nombre' => 'BOLSAS SIN PERFORAR',
            'estado_produccions_id' => 5,
        ]);
        ProcesoEstadoProduccion::create([
            'nombre' => 'PENDIENTE LISTON',
            'estado_produccions_id' => 5,
        ]);
        ProcesoEstadoProduccion::create([
            'nombre' => 'PAQUETE INCOMPLETO',
            'estado_produccions_id' => 5,
        ]);
        ProcesoEstadoProduccion::create([
            'nombre' => 'FINALIZADO/REVISADO',
            'estado_produccions_id' => 5,
        ]);

        //---------------
        ProcesoEstadoProduccion::create([
            'nombre' => 'FALTA DE MATERIA PRIMA ',
            'estado_produccions_id' => 6,
        ]);
        ProcesoEstadoProduccion::create([
            'nombre' => 'FALTA DE ACETATO ',
            'estado_produccions_id' => 6,
        ]);
        ProcesoEstadoProduccion::create([
            'nombre' => 'EN PROCESO ',
            'estado_produccions_id' => 6,
        ]);
        ProcesoEstadoProduccion::create([
            'nombre' => 'FINALIZADO/REVISADO',
            'estado_produccions_id' => 6,
        ]);
        

        //---------------
        ProcesoEstadoProduccion::create([
            'nombre' => 'DESHECHAS',
            'estado_produccions_id' => 7,
        ]);
        ProcesoEstadoProduccion::create([
            'nombre' => 'EN PROCESO',
            'estado_produccions_id' => 7,
        ]);
        ProcesoEstadoProduccion::create([
            'nombre' => 'INCOMPLETAS',
            'estado_produccions_id' => 7,
        ]);
        ProcesoEstadoProduccion::create([
            'nombre' => 'FALTA DE PERFORACIÓN',
            'estado_produccions_id' => 7,
        ]);
        ProcesoEstadoProduccion::create([
            'nombre' => 'FINALIZADO/REVISADO',
            'estado_produccions_id' => 7,
        ]);
        //-----------------------------

        ProcesoEstadoProduccion::create([
            'nombre' => 'EN AREA DE REVISION',
            'estado_produccions_id' => 8,
        ]);
        ProcesoEstadoProduccion::create([
            'nombre' => 'PROCESO EMPAQUE ',
            'estado_produccions_id' => 8,
        ]);
        ProcesoEstadoProduccion::create([
            'nombre' => 'EMPACADO-FINALIZADO - FALTA DE GUIA',
            'estado_produccions_id' => 8,
        ]);
        ProcesoEstadoProduccion::create([
            'nombre' => 'FALTA DE GUIA',
            'estado_produccions_id' => 8,
        ]);
        ProcesoEstadoProduccion::create([
            'nombre' => 'PAQUETE ENVIADO',
            'estado_produccions_id' => 8,
        ]);
        ProcesoEstadoProduccion::create([
            'nombre' => 'PAQUETE ATRASADO ',
            'estado_produccions_id' => 8,
        ]);
        ProcesoEstadoProduccion::create([
            'nombre' => 'PAQUETE EN ELABORACION ',
            'estado_produccions_id' => 8,
        ]);
        ProcesoEstadoProduccion::create([
            'nombre' => 'AREA DE IMPRESIÓN ',
            'estado_produccions_id' => 8,
        ]);
        ProcesoEstadoProduccion::create([
            'nombre' => 'SIN VERIFICAR ',
            'estado_produccions_id' => 8,
        ]);
        ProcesoEstadoProduccion::create([
            'nombre' => 'NO ENVIADO/ FALTA DE PAGO',
            'estado_produccions_id' => 8,
        ]);
        ProcesoEstadoProduccion::create([
            'nombre' => 'FINALIZADO/REVISADO',
            'estado_produccions_id' => 8,
        ]);

        // TERCERO DETALLE DE ESTADOS /-----------------------------------------------------------
        DetalleEstadoProduccion::create([
            'estado_produccions_id' => 1,
            'tipo' => 'fecha',
            'nombre' => 'FECHA ENTREGA',
        ]);
        DetalleEstadoProduccion::create([
            'estado_produccions_id' => 1,
            'tipo' => 'entero',
            'nombre' => 'PLIEGOS EXTRAS',
        ]);
        //--------
        DetalleEstadoProduccion::create([
            'estado_produccions_id' => 2,
            'tipo' => 'fecha',
            'nombre' => 'FECHA IMPRESIÓN',
        ]);
        DetalleEstadoProduccion::create([
            'estado_produccions_id' => 2,
            'tipo' => 'entero',
            'nombre' => 'UNIDAD IMPRESIÓN',
        ]);
        DetalleEstadoProduccion::create([
            'estado_produccions_id' => 2,
            'tipo' => 'entero',
            'nombre' => 'IMPRESIÓN EXTRA',
        ]);
        DetalleEstadoProduccion::create([
            'estado_produccions_id' => 2,
            'tipo' => 'entero',
            'nombre' => 'OTROS',
        ]);
        DetalleEstadoProduccion::create([
            'estado_produccions_id' => 2,
            'tipo' => 'entero',
            'nombre' => 'UNIDADES DESECHADAS',
        ]);

        //-----------------
        DetalleEstadoProduccion::create([
            'estado_produccions_id' => 3,
            'tipo' => 'entero',
            'nombre' => 'CANTIDAD',
        ]);
        DetalleEstadoProduccion::create([
            'estado_produccions_id' => 3,
            'tipo' => 'fecha',
            'nombre' => 'FECHA CORTE',
        ]);
        DetalleEstadoProduccion::create([
            'estado_produccions_id' => 3,
            'tipo' => 'entero',
            'nombre' => 'DESECHADAS',
        ]);
        //----------------
        DetalleEstadoProduccion::create([
            'estado_produccions_id' => 4,
            'tipo' => 'fecha',
            'nombre' => 'FECHA INICIAL ELABORACIÓN',
        ]);
        DetalleEstadoProduccion::create([
            'estado_produccions_id' => 4,
            'tipo' => 'fecha',
            'nombre' => 'FECHA FINAL ELABORACIÓN',
        ]);
        DetalleEstadoProduccion::create([
            'estado_produccions_id' => 4,
            'tipo' => 'entero',
            'nombre' => 'UNIDAD DE BOLSAS',
        ]);
        DetalleEstadoProduccion::create([
            'estado_produccions_id' => 4,
            'tipo' => 'entero',
            'nombre' => 'ETIQUETAS/TARJETAS',
        ]);
        DetalleEstadoProduccion::create([
            'estado_produccions_id' => 4,
            'tipo' => 'entero',
            'nombre' => 'UNIDADES DESECHADAS',
        ]);
        DetalleEstadoProduccion::create([
            'estado_produccions_id' => 4,
            'tipo' => 'entero',
            'nombre' => 'UNIDADES EXTRAS',
        ]);
        //----------------
        DetalleEstadoProduccion::create([
            'estado_produccions_id' => 5,
            'tipo' => 'fecha',
            'nombre' => 'FECHA INICIAL ELABORACIÓN',
        ]);
        DetalleEstadoProduccion::create([
            'estado_produccions_id' => 5,
            'tipo' => 'fecha',
            'nombre' => 'FECHA FINAL ELABORACIÓN',
        ]);
        DetalleEstadoProduccion::create([
            'estado_produccions_id' => 5,
            'tipo' => 'entero',
            'nombre' => 'UNIDAD DE BOLSAS',
        ]);
        DetalleEstadoProduccion::create([
            'estado_produccions_id' => 5,
            'tipo' => 'entero',
            'nombre' => 'ETIQUETAS/TARJETAS',
        ]);
        DetalleEstadoProduccion::create([
            'estado_produccions_id' => 5,
            'tipo' => 'entero',
            'nombre' => 'UNIDADES DESECHADAS',
        ]);
        DetalleEstadoProduccion::create([
            'estado_produccions_id' => 5,
            'tipo' => 'entero',
            'nombre' => 'UNIDADES EXTRAS',
        ]);

        //----------
        DetalleEstadoProduccion::create([
            'estado_produccions_id' => 8,
            'tipo' => 'FECHA',
            'nombre' => 'FECHA DE EMPAQUE',
        ]);
        DetalleEstadoProduccion::create([
            'estado_produccions_id' => 8,
            'tipo' => 'entero',
            'nombre' => 'UNIDADES DESECHADAS',
        ]);
        DetalleEstadoProduccion::create([
            'estado_produccions_id' => 8,
            'tipo' => 'entero',
            'nombre' => 'UNIDADES  EXTRAS',
        ]);
        DetalleEstadoProduccion::create([
            'estado_produccions_id' => 8,
            'tipo' => 'entero',
            'nombre' => 'UNIDAD FALTANTE',
        ]);
        DetalleEstadoProduccion::create([
            'estado_produccions_id' => 8,
            'tipo' => 'entero',
            'nombre' => 'TOTAL, BOLSAS ENVIADAS',
        ]);
        DetalleEstadoProduccion::create([
            'estado_produccions_id' => 8,
            'tipo' => 'entero',
            'nombre' => 'OTROS',
        ]);
    }
}
