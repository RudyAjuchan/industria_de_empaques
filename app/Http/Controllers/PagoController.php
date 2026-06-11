<?php

namespace App\Http\Controllers;

use App\Exports\PagosPendientesExport;
use App\Models\Pago;
use App\Models\Venta;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Barryvdh\DomPDF\Facade\Pdf;
use Maatwebsite\Excel\Facades\Excel;

class PagoController extends Controller
{
    public function index(Request $request)
    {
        return $this->creditosQuery($request)
            ->get()
            ->filter(fn ($v) => $this->filtrarPorEstadoCredito($v, 'vigentes'))
            ->values()
            ->map(function ($venta) {
                $venta->setAttribute('negocio_logotipo', $this->negocioLogotipo($venta));
                return $venta;
            });
    }

    private function creditosQuery(Request $request)
    {
        $search = $request->input('search');
        $search = ($search === 'null') ? null : $search;

        return Venta::with('pagos.banco', 'vendedor', 'cliente', 'pagina', 'detalles')
            ->withSum('pagos as total_pagado', 'monto')
            ->where('estado', 'emitida')
            ->when($search, function ($query) use ($search) {
                $query->where(function ($q) use ($search) {
                    $q->where('numero', 'like', "%{$search}%")
                        ->orWhereHas('cliente', fn ($cliente) => $cliente->where('nombre', 'like', "%{$search}%"))
                        ->orWhereHas('vendedor', fn ($vendedor) => $vendedor->where('name', 'like', "%{$search}%"))
                        ->orWhereHas('pagina', fn ($pagina) => $pagina->where('nombre', 'like', "%{$search}%"))
                        ->orWhereHas('detalles', fn ($detalle) => $detalle->where('nombre_logo', 'like', "%{$search}%"));
                });
            });
    }

    private function filtrarPorEstadoCredito(Venta $venta, string $estadoCredito): bool
    {
        $estadoCredito = strtolower($estadoCredito);
        $saldoPendiente = (float) $venta->saldo_pendiente;
        $creditoInicial = (float) ($venta->pendiente_pagar ?? 0);

        return match ($estadoCredito) {
            'generados' => $creditoInicial > 0,
            'pagados' => $creditoInicial > 0 && $saldoPendiente <= 0,
            'todos' => $creditoInicial > 0 || $saldoPendiente > 0,
            default => $saldoPendiente > 0,
        };
    }

    private function filtrarPorRangoFecha(Venta $venta, Request $request): bool
    {
        $fecha = optional($venta->created_at)->toDateString();

        if (!$fecha) {
            return false;
        }

        $desde = $request->input('desde');
        $hasta = $request->input('hasta');

        if ($desde && $fecha < $desde) {
            return false;
        }

        if ($hasta && $fecha > $hasta) {
            return false;
        }

        return true;
    }

    private function negocioLogotipo(Venta $venta): string
    {
        $logos = $venta->detalles
            ->pluck('nombre_logo')
            ->filter()
            ->map(fn ($logo) => trim($logo))
            ->filter()
            ->unique()
            ->values();

        if ($logos->isEmpty()) {
            return '-';
        }

        if ($logos->count() <= 2) {
            return $logos->implode(', ');
        }

        return $logos->take(2)->implode(', ') . ' (+' . ($logos->count() - 2) . ')';
    }
    public function store(Request $request)
    {
        $request->validate([
            'ventas_id' => 'required|exists:ventas,id',
            'monto' => 'required|numeric|min:0.01',
            'metodo_pago' => 'required|string',
            'referencia' => 'nullable|string',
            'banco_id' => 'nullable|exists:bancos,id',
            'comprobante' => 'nullable|file|mimes:jpg,jpeg,png,pdf,webp|max:5120',
            'nota' => 'nullable|string|max:1000',
        ]);

        $venta = Venta::findOrFail($request->ventas_id);

        $saldo = $venta->saldo_pendiente;

        if ($request->monto > $saldo) {
            return response()->json([
                'message' => 'El monto excede el saldo pendiente'
            ], 422);
        }

        $comprobantePath = $request->hasFile('comprobante')
            ? $request->file('comprobante')->store('pagos/comprobantes', 's3')
            : null;

        Pago::create([
            'ventas_id' => $venta->id,
            'monto' => $request->monto,
            'metodo_pago' => $request->metodo_pago,
            'referencia' => $request->referencia,
            'comprobante_path' => $comprobantePath,
            'nota' => $request->nota,
            'users_id' => Auth::user()->id,
            'bancos_id' => $request->banco_id
        ]);

        return response()->json([
            'message' => 'Pago registrado correctamente'
        ]);
    }

    public function show($ventaId)
    {
        return Pago::where('ventas_id', $ventaId)
            ->with('banco')
            ->latest()
            ->get();
    }


    public function exportPdf(Request $request)
    {
        $search = $request->input('search');
        $search = ($search === "null") ? null : $search;
        $estadoCredito = $request->input('estado_credito', 'vigentes');

        $ventas = $this->creditosQuery($request)
            ->get()
            ->filter(fn($v) => $this->filtrarPorRangoFecha($v, $request))
            ->filter(fn($v) => $this->filtrarPorEstadoCredito($v, $estadoCredito))
            ->values()
            ->map(function ($venta) {
                $venta->setAttribute('negocio_logotipo', $this->negocioLogotipo($venta));
                return $venta;
            });
        $filtros = [
            'desde' => $request->input('desde'),
            'hasta' => $request->input('hasta'),
            'estado_credito' => $estadoCredito,
        ];

        return Pdf::loadView('pdf.pagos_pendientes', compact('ventas', 'search', 'filtros'))
            ->setPaper('letter', 'landscape')
            ->stream('creditos_vigentes.pdf');
    }

    public function exportExcel(Request $request)
    {
        $search = $request->query('search');

        return Excel::download(
            new PagosPendientesExport(
                $search,
                $request->query('desde'),
                $request->query('hasta'),
                $request->query('estado_credito', 'vigentes')
            ),
            'creditos_vigentes.xlsx'
        );
    }

    public function delete(Request $request)
    {
        $request->validate([
            'id' => 'required|exists:pagos,id',
        ]);

        $pago = Pago::findOrFail($request->id);
        if ($pago->comprobante_path) {
            Storage::disk('s3')->delete($pago->comprobante_path);
        }

        $pago->delete();

        return response()->json(['message' => 'Pago eliminado correctamente']);
    }

    public function comprobante(Pago $pago)
    {
        abort_if(!$pago->comprobante_path, 404);

        $url = Storage::disk('s3')->temporaryUrl(
            $pago->comprobante_path,
            now()->addMinutes(10)
        );

        return redirect()->away($url);
    }
}
