<?php

namespace App\Http\Requests;

use App\Models\Producto;
use Illuminate\Foundation\Http\FormRequest;

class StoreVentaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'clientes_id' => ['required', 'exists:clientes,id'],
            'bancos_id' => ['nullable', 'exists:bancos,id'],
            'fecha_entrega' => ['required', 'date'],
            'tipo_pago' => ['required', 'string'],

            'cantidad_deposito' => ['nullable', 'numeric'],
            'costo_logo' => ['nullable', 'numeric'],
            'descuento' => ['nullable', 'numeric'],
            'promociones' => ['nullable', 'array'],
            'costo_envio' => ['nullable', 'numeric'],

            'detalle' => ['required', 'array', 'min:1'],

            'detalle.*.productos_id' => ['required', 'exists:productos,id'],
            'detalle.*.precio' => ['required', 'numeric', 'gt:0'],
            'detalle.*.cantidad' => ['required', 'integer', 'gt:0'],

            // ahora opcionales
            'detalle.*.tipo_agarradors_id' => ['nullable', 'exists:tipo_agarradors,id'],
            'detalle.*.tipo_papels_id' => ['nullable', 'exists:tipo_papels,id'],
            'detalle.*.color_agarrador' => ['nullable', 'string'],
            'detalle.*.detalle_impresion' => ['nullable', 'string'],
            'detalle.*.nombre_logo' => ['nullable', 'string'],
            'detalle.*.logo_path' => ['nullable', 'string'],

            'detalle.*.promocion_aplicada' => ['nullable', 'array'],
        ];
    }

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {

            foreach ($this->detalle as $index => $item) {

                $producto = Producto::find($item['productos_id']);

                if (!$producto) continue;

                // SOLO si es personalizado
                if ($producto->tipo_producto === 'personalizado') {

                    if (empty($item['tipo_agarradors_id'])) {
                        $validator->errors()->add("detalle.$index.tipo_agarradors_id", 'El tipo de agarrador es obligatorio');
                    }

                    if (empty($item['tipo_papels_id'])) {
                        $validator->errors()->add("detalle.$index.tipo_papels_id", 'El tipo de papel es obligatorio');
                    }

                    if (empty($item['color_agarrador'])) {
                        $validator->errors()->add("detalle.$index.color_agarrador", 'El color del agarrador es obligatorio');
                    }

                    if (empty($item['detalle_impresion'])) {
                        $validator->errors()->add("detalle.$index.detalle_impresion", 'El detalle de impresión es obligatorio');
                    }

                    if (empty($item['nombre_logo'])) {
                        $validator->errors()->add("detalle.$index.nombre_logo", 'El nombre del logo es obligatorio');
                    }
                }
            }
        });
    }

    public function messages(): array
    {
        return [
            'clientes_id.required' => 'Selecciona un cliente',
            'detalle.required' => 'Debe agregar al menos un producto',

            'detalle.*.productos_id.required' => 'Selecciona un producto',
            'detalle.*.precio.required' => 'El precio es obligatorio',
            'detalle.*.cantidad.required' => 'La cantidad es obligatoria',
            'detalle.*.precio.gt' => 'El precio debe ser mayor a cero',
            'detalle.*.cantidad.gt' => 'La cantidad debe ser mayor a cero',
        ];
    }
}
