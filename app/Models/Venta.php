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
        'estado_produccion',
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

    // Función para ver en que estado se encuentra la venta
    public function recalcularEstadoProduccion()
    {
        $detalles = $this->detalles()->with('historialEstados')->get();

        if ($detalles->isEmpty()) {
            $this->estado_produccion = 'sin_iniciar';
            $this->save();
            return;
        }

        $ultimoEstado = EstadoProduccion::orderByDesc('orden')->first();

        $todosSinIniciar = true;
        $todosFinalizados = true;

        foreach ($detalles as $detalle) {

            $historial = $detalle->historialEstados;

            // Ver si inició algún estado
            $tieneEntrada = $historial->contains(
                fn($h) =>
                $h->tipo_evento === 'entrada_estado'
            );

            if ($tieneEntrada) {
                $todosSinIniciar = false;
            }

            // Ver si finalizó el último estado
            $finalizoUltimo = $historial->contains(
                fn($h) =>
                $h->tipo_evento === 'finalizacion_estado'
                    && $h->estado_produccions_id === $ultimoEstado->id
            );

            if (!$finalizoUltimo) {
                $todosFinalizados = false;
            }
        }

        if ($todosSinIniciar) {
            $this->estado_produccion = 'sin_iniciar';
        } elseif ($todosFinalizados) {
            $this->estado_produccion = 'finalizada';
        } else {
            $this->estado_produccion = 'en_produccion';
        }

        $this->save();
    }

}
