<?php

namespace App\Services;

use App\Models\AsistenteVentas; // Asegúrate de tener este modelo
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Illuminate\http\Request;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class asistenteVentasLogic
{
    /**
     * Create a new class instance.
     */
    public function __construct()
    {
        //
    }

    /**
     * Registra un nuevo asistente de ventas.
     *
     * @param string $cedula
     * @param string $nombre
     * @param string $email
     * @param string $password
     * @param int $edad
     * @param string $sexo
     * @param float $salario
     * @return \Illuminate\Http\JsonResponse
     */
    public function registrar(string $cedula, string $nombre, string $email, string $password, int $edad, string $sexo, float $salario, ?string $urlImage = null)
    {
        $asistente = AsistenteVentas::create([
            'cedula' => $cedula,
            'nombre' => $nombre,
            'email' => $email,
            'password' => Hash::make($password),
            'edad' => $edad,
            'sexo' => $sexo,
            'salario' => $salario,
            'urlImage' => $urlImage,
            "created_at" => now(),
            "updated_at" => null,
        ]);

       $asistente->assignRole('asistente_ventas'); // Asigna el rol 'asistenteVentas' al asistente 

        return response()->json([
            'message' => 'Registro de asistente de ventas exitoso',
            'user' => $asistente,
        ], 201);
    }

    /**
     * Inicia sesión un asistente de ventas con email y contraseña.
     *
     * @param string $email
     * @param string $password
     * @return \Illuminate\Http\JsonResponse
     */
    public function login(string $email, string $password)
    {
        $asistente = AsistenteVentas::where('email', $email)->first();

        if (!$asistente || !Hash::check($password, $asistente->password)) {
            return response()->json([
                'message' => 'Credenciales inválidas.'
            ], 401);
        }

        $asistente->tokens()->where('name', 'asistente-auth-token')->delete();

        $token = $asistente->createToken('asistente-auth-token')->plainTextToken;

        $roleName = $asistente->getRoleNames()->first(); // Obtiene el nombre del primer rol
        $permissions = $asistente->getAllPermissions()->pluck('name'); // Obtiene todos los permisos por nombre

        return response()->json([
            'message' => 'Inicio de sesión de asistente de ventas exitoso',
            'user' => $asistente,
            'token' => $token,
            'role' => $roleName,
            'permissions' => $permissions
        ], 200);
    }

    /**
     * Cierra la sesión del asistente de ventas autenticado.
     *
     * @param AsistenteVentas $asistente
     * @return \Illuminate\Http\JsonResponse
     */
    public function logout(AsistenteVentas $asistente)
    {
        $asistente->currentAccessToken()->delete();
        return response()->json(['message' => 'Sesión cerrada correctamente'], 200); // 200 OK
    }

    /**
     * Obtiene los datos del asistente de ventas autenticado.
     *
     * @param AsistenteVentas $asistente
     * @return \Illuminate\Http\JsonResponse
     */
    public function getAutenticado(AsistenteVentas $asistente)
    {
        $roleName = $asistente->getRoleNames()->first(); // Obtiene el nombre del primer rol
        $permissions = $asistente->getAllPermissions()->pluck('name'); // Obtiene todos los permisos por nombre
        return response()->json([
            'message' => 'Datos del asistente de ventas',
            'data' => $asistente,
            'role' => $roleName,
            'permissions' => $permissions
        ], 200); // 200 OK
    }

    /**
     * Obtiene todos los asistentes de ventas.
     *
     * @return \Illuminate\Http\JsonResponse
     */

    public function getAll()
    {
        $asistentes = AsistenteVentas::all();
        return response()->json([
            'message' => 'Lista de asistentes de ventas',
            'data' => $asistentes
        ], 200); // 200 OK
    }

    /**
     * Elimina un asistente de ventas por su cédula.
     *
     * @param string $cedula
     * @return \Illuminate\Http\JsonResponse
     */

    public function delete(string $cedula)
    {
        $asistente = AsistenteVentas::where('cedula', $cedula)->first();

        if (!$asistente) {
            return response()->json(['message' => 'Asistente de ventas no encontrado'], 404);
        }

        $asistente->delete();
        return response()->json(['message' => 'Asistente de ventas eliminado correctamente'], 200); // 200 OK
    }

    /**
     * Actualiza los datos de un asistente de ventas.
     *
     * @param string $cedula
     * @param array $data
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(string $cedula, array $data)
    {
        $asistente = AsistenteVentas::where('cedula', $cedula)->first();

        if (!$asistente) {
            return response()->json(['message' => 'Asistente de ventas no encontrado'], 404);
        }

        // Actualiza los campos que se proporcionan
        $asistente->update(array_filter($data)); // array_filter elimina los campos vacíos

        return response()->json([
            'message' => 'Asistente de ventas actualizado correctamente',
            'data' => $asistente
        ], 200); // 200 OK
    }

    /**
     * Obtiene un asistente de ventas por su cédula.
     *
     * @param string $cedula
     * @return \Illuminate\Http\JsonResponse
     */
    public function getByCedula(string $cedula)
    {
        $asistente = AsistenteVentas::where('cedula', $cedula)->first();

        if (!$asistente) {
            return response()->json(['message' => 'Asistente de ventas no encontrado'], 404);
        }

        return response()->json([
            'message' => 'Datos del asistente de ventas',
            'data' => $asistente
        ], 200); // 200 OK
    }

    public function getByEmail(string $email)
    {
        $asistente = AsistenteVentas::where('email', $email)->first();
        if (!$asistente) {
            return response()->json(['message' => 'Asistente de ventas no encontrado'], 404);
        }
        return response()->json([
            'message' => 'Datos del asistente de ventas',
            'data' => $asistente
        ], 200);
    }

    public function getByNombre(string $nombre)
    {
        $asistentes = AsistenteVentas::where('nombre', 'like', "%$nombre%")->get();
        if ($asistentes->isEmpty()) {
            return response()->json(['message' => 'No se encontraron asistentes de ventas con ese nombre'], 404);
        }
        return response()->json([
            'message' => 'Lista de asistentes de ventas',
            'data' => $asistentes
        ], 200);
    }
}
