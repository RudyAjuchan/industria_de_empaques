<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Cliente;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

class ClienteAuthController extends Controller
{
    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required'
        ]);

        if (!Auth::guard('cliente')->attempt($credentials)) {
            return response()->json([
                'message' => 'Credenciales incorrectas'
            ], 401);
        }

        $request->session()->regenerate();

        return response()->json([
            'message' => 'Login exitoso',
            'cliente' => Auth::guard('cliente')->user()
        ]);
    }

    public function me()
    {
        return response()->json(Auth::guard('cliente')->user());
    }

    public function logout(Request $request)
    {
        Auth::guard('cliente')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return response()->json([
            'message' => 'Logout exitoso'
        ]);
    }

    public function register(Request $request)
    {
        $data = $request->validate([
            'nombre' => 'required|string|max:255',
            'genero' => 'required|string|max:20',
            'email' => 'required|email|unique:clientes,email',
            'password' => 'required|min:6|confirmed',

            // 🌍 DIRECCIÓN
            'pais' => 'required|string',
            'municipios_id' => 'nullable|exists:municipios,id',
            'estado_pais' => 'nullable|string|max:255',
            'ciudad_pais' => 'nullable|string|max:255',
            'direccion' => 'required|string',

            // 📞 TELÉFONO
            'telefono_codigo_pais' => 'required|string|max:5',
            'telefono_numero' => 'required|digits_between:8,20',
        ]);

        return DB::transaction(function () use ($data, $request) {

            // 🔥 CREAR CLIENTE
            $cliente = Cliente::create([
                'nombre' => $data['nombre'],
                'genero' => $data['genero'],
                'email' => $data['email'],
                'password' => Hash::make($data['password']),

                // ubicación
                'pais' => $data['pais'],
                'municipios_id' => $data['municipios_id'] ?? null,
                'estado_pais' => $data['estado_pais'] ?? null,
                'ciudad_pais' => $data['ciudad_pais'] ?? null,
                'direccion' => $data['direccion'],
            ]);

            // TELÉFONO
            $cliente->telefonos()->create([
                'telefono_codigo_pais' => $data['telefono_codigo_pais'],
                'telefono_numero' => $data['telefono_numero'],
            ]);

            // EMAIL
            $cliente->emails()->create([
                'email' => $data['email']
            ]);

            // LOGIN AUTOMÁTICO
            Auth::guard('cliente')->login($cliente);
            $request->session()->regenerate();

            // RELACIONES
            $cliente->load([
                'telefonos',
                'emails',
                'municipio.departamento'
            ]);

            return response()->json([
                'message' => 'Registro exitoso',
                'cliente' => $cliente
            ]);
        });
    }
}
