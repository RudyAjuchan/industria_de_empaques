<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HistorialEstadoProduccion extends Model
{
    protected $table = 'historial_estado_produccions';

    protected $fillable = [
        'detalle_ventas_id',
        'estado_produccions_id',
        'proceso_estado_produccions_id',
        'users_id',
        'fecha_inicio',
        'fecha_fin',
        'observacion',
        'tipo_evento',
    ];

    protected $casts = [
        'fecha_inicio' => 'datetime',
        'fecha_fin' => 'datetime',
    ];

    // Producto (detalle de venta)
    public function detalleVenta()
    {
        return $this->belongsTo(DetalleVenta::class, 'detalle_ventas_id');
    }

    // Estado macro
    public function estadoProduccion()
    {
        return $this->belongsTo(EstadoProduccion::class, 'estado_produccions_id');
    }

    // Subestado
    public function procesoEstado()
    {
        return $this->belongsTo(
            ProcesoEstadoProduccion::class,
            'proceso_estado_produccions_id'
        );
    }

    // Usuario responsable
    public function usuario()
    {
        return $this->belongsTo(User::class, 'users_id');
    }
}
