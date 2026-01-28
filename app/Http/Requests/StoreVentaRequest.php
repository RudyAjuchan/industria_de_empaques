<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreVentaRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'clientes_id' => ['required', 'exists:clientes,id'],
            'bancos_id' => ['required', 'exists:bancos,id'],
            'fecha_entrega' => ['required', 'date'],
            'tipo_pago' => ['required', 'string'],
            'cantidad_deposito' => ['nullable', 'numeric'],
            'costo_logo' => ['nullable', 'numeric'],
            'descuento' => ['nullable', 'numeric'],
            'promociones' => ['nullable', 'numeric'],
            'costo_envio' => ['nullable', 'numeric'],

            'detalle' => ['required', 'array', 'min:1'],

            'detalle.*.productos_id' => ['required', 'exists:productos,id'],
            'detalle.*.color_agarrador' => ['required', 'string'],
            'detalle.*.detalle_impresion' => ['required', 'string'],
            'detalle.*.nombre_logo' => ['required', 'string'],
            'detalle.*.precio' => ['required', 'numeric', 'gt:0'],
            'detalle.*.cantidad' => ['required', 'integer', 'gt:0'],
            'detalle.*.total' => ['required', 'numeric', 'gt:0'],

            'detalle.*.tipo_agarradors_id' => ['required', 'exists:tipo_agarradors,id'],
            'detalle.*.tipo_papels_id' => ['required', 'exists:tipo_papels,id'],
        ];
    }

    public function messages(): array
    {
        return [
            'clientes_id.required' => 'Por favor, selecciona un cliente para continuar.',
            'bancos_id.required'   => 'Por favor, selecciona un banco para continuar.',
            'detalle.*.productos_id.required' => 'Por favor, selecciona un producto',
            'detalle.*.color_agarrador.required' => 'Color agarrador es obligatorio',
            'detalle.*.detalle_impresion.required' => 'Detalle impresión es obligatorio',
            'detalle.*.nombre_logo.required' => 'Nombre logo es obligatorio',
            'detalle.*.precio.required' => 'El precio es obligatorio',
            'detalle.*.cantidad.required' => 'La cantidad es obligatorio',
            'detalle.*.precio.gt' => 'El precio debe ser mayor a cero',
            'detalle.*.cantidad.gt' => 'El precio debe ser mayor a cero',
            'detalle.*.tipo_agarradors_id.required' => 'Por favor, selecciona un tipo de agarrador',
            'detalle.*.tipo_papels_id.required' => 'Por favor, selecciona un tipo de papel',
        ];
    }
}
