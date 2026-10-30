<?php

namespace App\Http\Controllers;

use App\Http\Requests\ChangePasswordRequest;
use App\Http\Requests\LoginRequest;
use App\Http\Requests\RegisterRequest;
use App\Models\Client;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use OpenApi\Attributes as OA;

class AuthController extends Controller
{
    #[OA\Post(
        path: "/register",
        summary: "Registrar un nuevo usuario",
        tags: ["Auth"],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ["name", "email", "password", "password_confirmation"],
                properties: [
                    new OA\Property(property: "name", type: "string", example: "Juan Guzman"),
                    new OA\Property(property: "email", type: "string", format: "email", example: "juan@test.com"),
                    new OA\Property(property: "password", type: "string", format: "password", example: "password123"),
                    new OA\Property(property: "password_confirmation", type: "string", format: "password", example: "password123"),
                ]
            )
        ),
        responses: [
            new OA\Response(response: 201, description: "Usuario registrado exitosamente"),
            new OA\Response(response: 422, description: "Error de validación"),
        ]
    )]
    public function register(RegisterRequest $request)
    {
        // Crear Client primero
        $client = Client::create([
            'name' => $request->name,
            'email' => $request->email,
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => $request->password,
            'client_id' => $client->id,
        ]);

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'role' => $user->role ?? 'user',
                'client_id' => $user->client_id,
            ],
            'token' => $token,
        ], 201);
    }

    #[OA\Post(
        path: "/login",
        summary: "Iniciar sesión (Sanctum)",
        tags: ["Auth"],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ["email", "password"],
                properties: [
                    new OA\Property(property: "email", type: "string", format: "email", example: "juan@test.com"),
                    new OA\Property(property: "password", type: "string", format: "password", example: "password123"),
                ]
            )
        ),
        responses: [
            new OA\Response(response: 200, description: "Login exitoso, devuelve token"),
            new OA\Response(response: 422, description: "Credenciales inválidas"),
        ]
    )]
    public function login(LoginRequest $request)
    {
        $user = User::where('email', $request->email)->first();

        if (! $user || ! Hash::check($request->password, $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['Las credenciales no coinciden con nuestros registros.'],
            ]);
        }

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'role' => $user->role ?? 'user',
                'client_id' => $user->client_id,
            ],
            'token' => $token,
        ]);
    }

    #[OA\Post(
        path: "/logout",
        summary: "Cerrar sesión (revoca el token actual)",
        tags: ["Auth"],
        security: [["bearerAuth" => []]],
        responses: [
            new OA\Response(response: 200, description: "Sesión cerrada correctamente"),
            new OA\Response(response: 401, description: "No autenticado"),
        ]
    )]
    public function logout()
    {
        request()->user()->currentAccessToken()->delete();

        return response()->json(['message' => 'Sesión cerrada correctamente.']);
    }

    #[OA\Put(
        path: "/change-password",
        summary: "Cambiar la contraseña del usuario autenticado",
        tags: ["Auth"],
        security: [["bearerAuth" => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ["current_password", "new_password", "new_password_confirmation"],
                properties: [
                    new OA\Property(property: "current_password", type: "string", format: "password", example: "password123"),
                    new OA\Property(property: "new_password", type: "string", format: "password", example: "newpassword456"),
                    new OA\Property(property: "new_password_confirmation", type: "string", format: "password", example: "newpassword456"),
                ]
            )
        ),
        responses: [
            new OA\Response(response: 200, description: "Contraseña actualizada correctamente"),
            new OA\Response(response: 401, description: "No autenticado"),
            new OA\Response(response: 422, description: "Error de validación (contraseña actual incorrecta, no coincide confirmación, o igual a la anterior)"),
        ]
    )]
    public function changePassword(ChangePasswordRequest $request)
    {
        $user = $request->user();

        if (! Hash::check($request->current_password, $user->password)) {
            throw ValidationException::withMessages([
                'current_password' => ['La contraseña actual no es correcta.'],
            ]);
        }

        $user->update([
            'password' => $request->new_password,
        ]);

        return response()->json([
            'message' => 'Contraseña actualizada correctamente.',
        ]);
    }
}