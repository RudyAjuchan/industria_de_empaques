<?php

namespace App\Http\Controllers;

use App\Exports\PagosPendientesExport;
use App\Models\Pago;
use App\Models\Venta;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Barryvdh\DomPDF\Facade\Pdf;
use Maatwebsite\Excel\Facades\Excel;

class PagoController extends Controller
{
    public function index(){
        return Venta::with('pagos.banco', 'vendedor', 'cliente')
        ->get()
        ->where('estado', 'emitida')
        ->filter(fn ($v) => $v->saldo_pendiente > 0)
        ->values();
    }
    public function store(Request $request)
    {
        $request->validate([
            'ventas_id' => 'required|exists:ventas,id',
            'monto' => 'required|numeric|min:0.01',
            'metodo_pago' => 'required|string',
            'referencia' => 'nullable|string',
            'banco_id' => 'nullable|exists:bancos,id',
        ]);

        $venta = Venta::findOrFail($request->ventas_id);

        $saldo = $venta->saldo_pendiente;

        if ($request->monto > $saldo) {
            return response()->json([
                'message' => 'El monto excede el saldo pendiente'
            ], 422);
        }

        Pago::create([
            'ventas_id' => $venta->id,
            'monto' => $request->monto,
            'metodo_pago' => $request->metodo_pago,
            'referencia' => $request->referencia,
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
            ->latest()
            ->get();
    }


    public function exportPdf(Request $request)
    {
        $search = $request->input('search');
        $search = ($search === "null") ? null : $search;

        $query = Venta::with(['pagos.banco', 'vendedor', 'cliente'])
            ->where('estado', 'emitida');

        if ($search) {
            $query->whereHas('cliente', function ($q) use ($search) {
                $q->where('nombre', 'like', "%{$search}%");
            });
        }

        $ventas = $query->get()
            ->filter(fn($v) => $v->saldo_pendiente > 0)
            ->values();
        return Pdf::loadView('pdf.pagos_pendientes', compact('ventas', 'search'))
            ->setPaper('letter', 'landscape')
            ->stream('pagos_pendientes.pdf');
    }

    public function exportExcel(Request $request)
    {
        $search = $request->query('search');

        return Excel::download(
            new PagosPendientesExport($search),
            'pagos_pendientes.xlsx'
        );
    }

    public function delete(Request $request)
    {
        $request->validate([
            'id' => 'required|exists:pagos,id',
        ]);

        $pago = Pago::findOrFail($request->id);
        $primerPagoId = Pago::where('ventas_id', $pago->ventas_id)
            ->oldest()
            ->oldest('id')
            ->value('id');

        if ($pago->id === $primerPagoId) {
            return response()->json([
                'message' => 'No se puede eliminar el pago inicial de la venta'
            ], 422);
        }

        $pago->delete();

        return response()->json(['message' => 'Pago eliminado correctamente']);
    }
}
