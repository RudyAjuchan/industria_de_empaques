<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Cliente;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules;

class ClientePasswordResetController extends Controller
{
    public function sendResetLink(Request $request): JsonResponse
    {
        $request->validate([
            'email' => ['required', 'email'],
        ]);

        $status = Password::broker('clientes')->sendResetLink(
            $request->only('email')
        );

        if ($status !== Password::RESET_LINK_SENT) {
            return response()->json([
                'message' => __($status),
                'errors' => [
                    'email' => [__($status)],
                ],
            ], 422);
        }

        return response()->json([
            'message' => 'Correo de restablecimiento enviado.',
        ]);
    }

    public function create(Request $request, string $token): RedirectResponse
    {
        $query = http_build_query([
            'token' => $token,
            'email' => $request->query('email'),
        ]);

        return redirect()->away($this->ecommerceResetUrl().'?'.$query);
    }

    public function reset(Request $request): JsonResponse
    {
        $request->validate([
            'token' => ['required'],
            'email' => ['required', 'email'],
            'password' => ['required', 'confirmed', $this->passwordRules()],
        ], $this->passwordMessages());

        $status = Password::broker('clientes')->reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function (Cliente $cliente) use ($request) {
                $cliente->forceFill([
                    'password' => Hash::make($request->password),
                    'remember_token' => Str::random(60),
                ])->save();

                event(new PasswordReset($cliente));
            }
        );

        if ($status !== Password::PASSWORD_RESET) {
            return response()->json([
                'message' => __($status),
                'errors' => [
                    'email' => [__($status)],
                ],
            ], 422);
        }

        return response()->json([
            'message' => 'Contraseña restablecida correctamente.',
        ]);
    }

    private function ecommerceResetUrl(): string
    {
        return rtrim(config('app.ecommerce_url'), '/').'/reset-password';
    }

    private function passwordRules(): Rules\Password
    {
        return Rules\Password::min(8)
            ->mixedCase()
            ->symbols();
    }

    private function passwordMessages(): array
    {
        return [
            'password.required' => 'La contraseña es obligatoria.',
            'password.confirmed' => 'La confirmación de contraseña no coincide.',
            'password.min' => 'La contraseña debe tener al menos 8 caracteres.',
            'password.mixed' => 'La contraseña debe incluir al menos una letra mayúscula y una minúscula.',
            'password.symbols' => 'La contraseña debe incluir al menos un símbolo.',
        ];
    }
}
