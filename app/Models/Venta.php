<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Venta extends Model
{
    protected $table = 'ventas';

    protected $fillable = [
        'vendedor_id',
        'clientes_id',
        'bancos_id',
        'serie',
        'numero',
        'fecha_entrega',
        'tipo_pago',
        'no_deposito',
        'cantidad_deposito',
        'pendiente_pagar',
        'costo_logo',
        'subtotal',
        'descuento',
        'promociones',
        'costo_envio',
        'total',
        'proceso_estado_produccions_id',
        'estado',
        'sat_uuid',
        'sat_fecha',
        'sat_certificador',
        'sat_respuesta',
    ];

    protected $casts = [
        'sat_fecha' => 'datetime',
        'sat_respuesta' => 'array',
    ];

    protected $appends = ['numero_completo'];

    public function getNumeroCompletoAttribute()
    {
        // str_pad añade ceros a la izquierda hasta llegar a 6 dígitos
        $numeroConCeros = str_pad($this->numero, 6, '0', STR_PAD_LEFT);
        
        return "{$this->serie}-{$numeroConCeros}";
    }

    // Vendedor
    public function vendedor()
    {
        return $this->belongsTo(User::class, 'vendedor_id');
    }

    // Cliente
    public function cliente()
    {
        return $this->belongsTo(Cliente::class, 'clientes_id');
    }

    // Banco
    public function banco()
    {
        return $this->belongsTo(Banco::class, 'bancos_id');
    }

    // Proceso de producción asignado
    public function procesoProduccion()
    {
        return $this->belongsTo(
            ProcesoEstadoProduccion::class,
            'proceso_estado_produccions_id'
        );
    }

    // Detalle de productos
    public function detalles()
    {
        return $this->hasMany(DetalleVenta::class, 'ventas_id');
    }
}
