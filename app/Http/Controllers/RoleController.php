<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;
use App\Exports\RoleExport;
use Maatwebsite\Excel\Facades\Excel;
use Barryvdh\DomPDF\Facade\Pdf;

class RoleController extends Controller
{
    public function index()
    {
        return Role::select('id', 'name')->get();
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|unique:roles,name'
        ]);

        return Role::create([
            'name' => $data['name'],
            'guard_name' => 'web'
        ]);
    }

    public function show(Role $role)
    {
        return $role->only('id', 'name');
    }

    public function update(Request $request, Role $role)
    {
        $data = $request->validate([
            'name' => 'required|string|unique:roles,name,' . $role->id
        ]);

        $role->update($data);

        return response()->json(['success' => true]);
    }

    public function destroy(Role $role)
    {
        if ($role->users()->count() > 0) {
            return response()->json([
                'message' => 'No se puede eliminar un rol con usuarios asignados'
            ], 422);
        }

        $role->delete();

        return response()->json(['success' => true]);
    }

    public function exportPdf(Request $request)
    {
        $search = trim((string) $request->query('search'));

        $roles = Role::with('permissions')
            ->when($search !== '', function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%");
            })
            ->orderBy('name')
            ->get();

        $pdf = Pdf::loadView('pdf.roles', [
            'roles' => $roles,
            'search' => $search,
        ])->setPaper('a4', 'portrait');

        return $pdf->stream('roles.pdf');
    }

    public function exportExcel(Request $request)
    {
        $search = $request->query('search');

        return Excel::download(
            new RoleExport($search),
            'roles.xlsx'
        );
    }
}
