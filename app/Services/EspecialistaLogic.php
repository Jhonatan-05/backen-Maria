<?php

namespace App\Services;

use App\Models\Especialista;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class EspecialistaLogic
{
    /**
     * Create a new class instance.
     */
    public function __construct()
    {
        //
    }

    /**
     * Registra un nuevo especialista.
     *
     * @param string $cedula
     * @param string $nombre
     * @param string $email
     * @param string $password
     * @param int $edad
     * @param string $sexo
     * @param string $rol
     * @param float $salario
     * @return \Illuminate\Http\JsonResponse
     */
    public function registrar(string $cedula, string $nombre, string $email, string $password, int $edad, string $sexo, string $rol, float $salario, ?string $urlImage = null)
    {
        $especialista = Especialista::create([
            'cedula' => $cedula,
            'nombre' => $nombre,
            'email' => $email,
            'password' => Hash::make($password),
            'edad' => $edad,
            'sexo' => $sexo,
            'rol' => $rol,
            'salario' => $salario,
            'urlImage' => $urlImage,
            "created_at" => now(),
            "updated_at" => null,
        ]);

        $especialista->assignRole('especialista');

        return response()->json([
            'message' => 'Registro de especialista exitoso',
            'user' => $especialista,
        ], 201);
    }

    /**
     * Inicia sesión un especialista con email y contraseña.
     *
     * @param string $email
     * @param string $password
     * @return \Illuminate\Http\JsonResponse
     */
    public function login(string $email, string $password)
    {

        $especialista = Especialista::where('email', $email)->first();

        if (!$especialista || !Hash::check($password, $especialista->password)) {
            return response()->json([
                'message' => 'Credenciales inválidas.'
            ], 401);
        }

        $especialista->tokens()->where('name', 'especialista-auth-token')->delete();

        $token = $especialista->createToken('especialista-auth-token')->plainTextToken;
        $roleName = $especialista->getRoleNames()->first(); // Obtiene el nombre del primer rol
        $permissions = $especialista->getAllPermissions()->pluck('name'); // Obtiene todos los permisos por nombre


        return response()->json([
            'message' => 'Inicio de sesión de especialista exitoso',
            'user' => $especialista,
            'token' => $token,
            'role' => $roleName,
            'permissions' => $permissions
        ], 200);
    }

    /**
     * Cierra la sesión del especialista.
     *
     * @param Especialista $especialista
     * @return \Illuminate\Http\JsonResponse
     */
    public function logout(Especialista $especialista)
    {
        $especialista->currentAccessToken()->delete();
        return response()->json(['message' => 'Sesión cerrada correctamente'], 200);
    }

    /**
     * Obtiene los datos del especialista autenticado.
     *
     * @param Especialista $especialista
     * @return \Illuminate\Http\JsonResponse
     */
    public function getAuth(Especialista $especialista)
    {
        $roleName = $especialista->getRoleNames()->first(); // Obtiene el nombre del primer rol
        $permissions = $especialista->getAllPermissions()->pluck('name'); // Obtiene todos los permisos por nombre
        return response()->json([
            'message' => 'Datos del especialista',
            'data' => $especialista,
            'role' => $roleName,
            'permissions' => $permissions
        ], 200);
    }

    /**
     * Obtiene todos los especialistas.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getAll()
    {
        $especialistas = Especialista::all();
        return response()->json([
            'message' => 'Lista de especialistas',
            'data' => $especialistas
        ], 200);
    }

    /**
     * Elimina un especialista por su cédula.
     *
     * @param string $cedula
     * @return \Illuminate\Http\JsonResponse
     */
    public function delete(string $cedula)
    {
        $especialista = Especialista::where('cedula', $cedula)->first();

        if (!$especialista) {
            return response()->json(['message' => 'Especialista no encontrado'], 404);
        }

        $especialista->delete();

        return response()->json(['message' => 'Especialista eliminado correctamente'], 200);
    }

    /**
     * Actualiza los datos de un especialista.
     *
     * @param Especialista $especialista
     * @param array $data
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(string $cedula, array $data)
    {
        $especialista = Especialista::where('cedula', $cedula)->first();

        if (!$especialista) {
            return response()->json(['message' => 'Especialista no encontrado'], 404);
        }
        // urlImage puede venir en $data
        $especialista->update($data);

        return response()->json([
            'message' => 'Especialista actualizado correctamente',
            'data' => $especialista
        ], 200);
    }

    /**
     * Obtiene un especialista por su cédula.
     *
     * @param string $cedula
     * @return \Illuminate\Http\JsonResponse
     */
    public function getByCedula(string $cedula)
    {
        $especialista = Especialista::where('cedula', $cedula)->first();

        if (!$especialista) {
            return response()->json(['message' => 'Especialista no encontrado'], 404);
        }

        return response()->json([
            'message' => 'Datos del especialista',
            'data' => $especialista
        ], 200);
    }

    public function getByEmail(string $email)
    {
        $especialista = Especialista::where('email', $email)->first();
        if (!$especialista) {
            return response()->json(['message' => 'Especialista no encontrado'], 404);
        }
        return response()->json([
            'message' => 'Datos del especialista',
            'data' => $especialista
        ], 200);
    }

    public function getByNombre(string $nombre)
    {
        $especialistas = Especialista::where('nombre', 'like', "%$nombre%")->get();
        if ($especialistas->isEmpty()) {
            return response()->json(['message' => 'No se encontraron especialistas con ese nombre'], 404);
        }
        return response()->json([
            'message' => 'Lista de especialistas',
            'data' => $especialistas
        ], 200);
    }
    // ...existing code...
}
