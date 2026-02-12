<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DetalleVenta extends Model
{
    protected $table = 'detalle_ventas';

    protected $fillable = [
        'ventas_id',
        'productos_id',
        'tipo_agarradors_id',
        'tipo_papels_id',
        'proceso_estado_produccions_id',
        'color_agarrador',
        'detalle_impresion',
        'nombre_logo',
        'precio',
        'cantidad',
        'total',
    ];

    // Venta
    public function venta()
    {
        return $this->belongsTo(Venta::class, 'ventas_id');
    }

    // Producto
    public function producto()
    {
        return $this->belongsTo(Producto::class, 'productos_id');
    }

    public function tipoAgarrador()
    {
        return $this->belongsTo(TipoAgarrador::class, 'tipo_agarradors_id');
    }

    public function tipoPapel()
    {
        return $this->belongsTo(TipoPapel::class, 'tipo_papels_id');
    }

    public function historialEstados()
    {
        return $this->hasMany(
            HistorialEstadoProduccion::class,
            'detalle_ventas_id'
        )->orderBy('fecha_inicio');
    }

    // Estado actual (el activo)
    public function getEstadoActual()
    {
        return $this->historialEstados()
            ->whereNull('fecha_fin')
            ->orderByDesc('fecha_inicio')
            ->first();
    }
    public function getEstadoActivo()
    {
        return $this->historialEstados()
            ->where('tipo_evento', 'entrada_estado')
            ->whereNull('fecha_fin')
            ->first();
    }


    public function getProcesoActivo()
    {
        return $this->historialEstados()
            ->whereNotNull('proceso_estado_produccions_id')
            ->whereNull('fecha_fin')
            ->orderByDesc('fecha_inicio')
            ->first();
    }
}
