<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Mail\ContactoMail;
use App\Models\Cliente;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;

class ClienteAuthController extends Controller
{
    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required'
        ]);

        if (!Auth::guard('cliente')->attempt([
            'email' => $request->email,
            'password' => $request->password,
            'estado' => 1,
        ])) {
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

    public function register(Request $request)
    {
        $data = $request->validate([
            'nombre' => 'required|string|max:255',
            'genero' => 'required|string|max:20',
            'email' => 'required|email',
            'password' => 'required|min:6|confirmed',

            'pais' => 'required|string',
            'municipios_id' => 'nullable|exists:municipios,id',
            'estado_pais' => 'nullable|string|max:255',
            'ciudad_pais' => 'nullable|string|max:255',
            'direccion' => 'required|string',

            'telefono_codigo_pais' => 'required|string|max:5',
            'telefono_numero' => 'required|digits_between:8,20',
        ]);

        return DB::transaction(function () use ($data, $request) {

            $cliente = Cliente::where('email', $data['email'])->first();

            // ===============================
            // NO EXISTE → CREAR
            // ===============================
            if (!$cliente) {

                $cliente = Cliente::create([
                    'nombre' => $data['nombre'],
                    'genero' => $data['genero'],
                    'email' => $data['email'],
                    'password' => Hash::make($data['password']),

                    'pais' => $data['pais'],
                    'municipios_id' => $data['municipios_id'] ?? null,
                    'estado_pais' => $data['estado_pais'] ?? null,
                    'ciudad_pais' => $data['ciudad_pais'] ?? null,
                    'direccion' => $data['direccion'],
                    'estado' => 1,
                ]);

                $cliente->telefonos()->create([
                    'codigo_pais' => $data['telefono_codigo_pais'],
                    'numero' => $data['telefono_numero'],
                ]);

                $cliente->emails()->create([
                    'email' => $data['email']
                ]);
            }

            // ===============================
            // EXISTE PERO INACTIVO → REACTIVAR
            // ===============================
            elseif ($cliente->estado == 0) {

                $cliente->update([
                    'nombre' => $data['nombre'],
                    'genero' => $data['genero'],
                    'password' => Hash::make($data['password']),

                    'pais' => $data['pais'],
                    'municipios_id' => $data['municipios_id'] ?? null,
                    'estado_pais' => $data['estado_pais'] ?? null,
                    'ciudad_pais' => $data['ciudad_pais'] ?? null,
                    'direccion' => $data['direccion'],
                    'estado' => 1, // 🔥 REACTIVAR
                ]);

                // actualizar o crear teléfono
                $cliente->telefonos()->updateOrCreate(
                    [], // primer registro
                    [
                        'codigo_pais' => $data['telefono_codigo_pais'],
                        'numero' => $data['telefono_numero'],
                    ]
                );

                // asegurar email
                $cliente->emails()->firstOrCreate([
                    'email' => $data['email']
                ]);
            }

            // ===============================
            // EXISTE SIN PASSWORD → COMPLETAR
            // ===============================
            elseif (!$cliente->password) {

                $cliente->update([
                    'nombre' => $data['nombre'],
                    'genero' => $data['genero'],
                    'password' => Hash::make($data['password']),

                    'pais' => $data['pais'],
                    'municipios_id' => $data['municipios_id'] ?? null,
                    'estado_pais' => $data['estado_pais'] ?? null,
                    'ciudad_pais' => $data['ciudad_pais'] ?? null,
                    'direccion' => $data['direccion'],
                    'estado' => 1,
                ]);

                $cliente->telefonos()->updateOrCreate(
                    [],
                    [
                        'codigo_pais' => $data['telefono_codigo_pais'],
                        'numero' => $data['telefono_numero'],
                    ]
                );

                $cliente->emails()->firstOrCreate([
                    'email' => $data['email']
                ]);
            }

            // ===============================
            // YA EXISTE ACTIVO → ERROR
            // ===============================
            else {
                return response()->json([
                    'message' => 'Este correo ya está registrado. Inicia sesión.'
                ], 422);
            }

            // ===============================
            // LOGIN AUTOMÁTICO
            // ===============================
            Auth::guard('cliente')->login($cliente);
            $request->session()->regenerate();

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

    public function update(Request $request)
    {
        $user = auth('cliente')->user();

        // UPDATE CLIENTE
        /** @var \App\Models\Cliente $user */
        $user->update($request->only([
            'nombre',
            'genero',
            'email',
            'pais',
            'municipios_id',
            'estado_pais',
            'ciudad_pais',
            'direccion',
            'nit',
            'dpi',
        ]));

        // TELÉFONO
        /** @var \App\Models\Cliente $user */
        if ($request->telefono_numero) {
            $telefono = $user->telefonos()->first();
            if ($telefono) {
                $telefono->update([
                    'telefono_codigo_pais' => $request->telefono_codigo_pais,
                    'telefono_numero' => $request->telefono_numero,
                ]);
            } else {
                $user->telefonos()->create([
                    'telefono_codigo_pais' => $request->telefono_codigo_pais,
                    'telefono_numero' => $request->telefono_numero,
                ]);
            }
        }

        return response()->json([
            'message' => 'Perfil actualizado',
            'user' => $user->load('municipio.departamento', 'telefonos')
        ]);
    }

    public function enviarContacto(Request $request)
    {
        $data = $request->validate([
            'nombre' => 'required|string|max:255',
            'email' => 'required|email',
            'mensaje' => 'required|string',
        ]);

        // Ejemplo: enviar correo
        Mail::to('rudyajuchansec44@gmail.com')->send(new ContactoMail($data));

        return response()->json(['message' => 'Mensaje enviado']);
    }
}
