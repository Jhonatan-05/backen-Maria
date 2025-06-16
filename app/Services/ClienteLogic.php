<?php

namespace App\Services;

use App\Models\Cliente; // Asegúrate de importar tu modelo
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class ClienteLogic
{
    /**
     * Create a new class instance.
     */
    public function __construct()
    {
        //
    }

    /**
     * Registra un nuevo cliente.
     *
     * @param string $cedula
     * @param string $nombre
     * @param string $email
     * @param string $password
     * @param int $edad
     * @param string $sexo
     * @return \Illuminate\Http\JsonResponse
     */
    public function registrar(string $cedula, string $nombre, string $email, string $password, int $edad, string $sexo, ?string $urlImage = null)
    {
        $cliente = Cliente::create([
            'cedula' => $cedula,
            'nombre' => $nombre,
            'email' => $email,
            'password' => Hash::make($password),
            'edad' => $edad,
            'sexo' => $sexo,
            'urlImage' => $urlImage,
            "created_at" => now(),
            "updated_at" => null,
        ]);

        $cliente->assignRole('cliente');

        return response()->json([
            'message' => 'Registro de cliente exitoso',
            'user' => $cliente
        ], 201); // Código 201 para "Creado"
    }

    /**
     * Inicia sesión un cliente con email y contraseña.
     *
     * @param string $email
     * @param string $password
     * @return \Illuminate\Http\JsonResponse
     */
    public function login(string $email, string $password)
    {

        $cliente = Cliente::where('email', $email)->first();

        // Intentar autenticar con el ORM
        if (!$cliente || !Hash::check($password, $cliente->password)) {
            return response()->json([
                'message' => 'Credenciales inválidas.'
            ], 401);
        }

        //Crea el token para este usuario
        $token = $cliente->createToken($email)->plainTextToken;

        $roleName = $cliente->getRoleNames()->first(); // Obtiene el nombre del primer rol
        $permissions = $cliente->getAllPermissions()->pluck('name'); // Obtiene todos los permisos por nombre

        return response()->json([
            'message' => 'Inicio de sesión de cliente exitoso',
            'user' => $cliente,
            'token' => $token,
            'role' => $roleName,
            'permissions' => $permissions
        ], 200); // 200 ok
    }

    /**
     * Cierra la sesión del cliente autenticado.
     *
     * @param Cliente $cliente
     * @return \Illuminate\Http\JsonResponse
     */
    public function logout(Cliente $cliente)
    {
        $cliente->currentAccessToken()->delete();
        return response()->json(['message' => 'Sesión cerrada correctamente'], 200);
    }

    /**
     * Obtiene los datos del cliente autenticado.
     *
     * @param Cliente $cliente
     * @return \Illuminate\Http\JsonResponse
     */
    public function getAutenticado(Cliente $cliente)
    {

        $roleName = $cliente->getRoleNames()->first(); // Obtiene el nombre del primer rol
        $permissions = $cliente->getAllPermissions()->pluck('name'); // Obtiene todos los permisos por nombre
        return response()->json([
            'message' => 'Datos del cliente',
            'data' => $cliente,
            'role'=> $roleName,
            'permissions' => $permissions
        ], 200); // 200 ok
    }


    /**
     * Elimina un cliente por su cédula.
     *
     * @param string $cedula
     * @return \Illuminate\Http\JsonResponse
     */
    public function delete(string $cedula)
    {
        $cliente = Cliente::where('cedula', $cedula)->first();

        if (!$cliente) {
            return response()->json(['message' => 'Cliente no encontrado'], 404);
        }

        $cliente->delete();

        return response()->json(['message' => 'Cliente eliminado correctamente'], 200);
    }

    /**
     * Obtiene todos los clientes.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getAll()
    {
        $clientes = Cliente::all();
        return response()->json([
            'message' => 'Lista de clientes',
            'data' => $clientes->toArray()
        ], 200); // 200 ok
    }

    /**
     * Actualiza los datos de un cliente por su cédula.
     *
     * @param string $cedula
     * @param string $nombre
     * @param string $email
     * @param int $edad
     * @param string $sexo
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(string $cedula, array $data)
    {
        $cliente = Cliente::where('cedula', $cedula)->first();

        if (!$cliente) {
            return response()->json(['message' => 'Cliente no encontrado'], 404);
        }

        // Manejar actualización de contraseña si se proporciona
        if (isset($data['password']) && !empty($data['password'])) {
            $data['password'] = Hash::make($data['password']);
        } else {
            unset($data['password']);
        }

        $cliente->fill($data);
        $cliente->save();
        return response()->json([
            'message' => 'Datos del cliente actualizados correctamente',
            'data' => $cliente->toArray()
        ], 200); // 200 ok
    }
    
    /**
     * Obtiene un cliente por su cédula.
     *
     * @param string $cedula
     * @return \Illuminate\Http\JsonResponse
     */
    public function getByCedula(string $cedula)
    {
        $cliente = Cliente::where('cedula', $cedula)->first();

        if (!$cliente) {
            return response()->json(['message' => 'Cliente no encontrado'], 404);
        }

        return response()->json([
            'message' => 'Datos del cliente',
            'data' => $cliente
        ], 200); // 200 ok
    }

    public function getByEmail(string $email)
    {
        $cliente = Cliente::where('email', $email)->first();

        if (!$cliente) {
            return response()->json(['message' => 'Cliente no encontrado'], 404);
        }

        return response()->json([
            'message' => 'Datos del cliente',
            'data' => $cliente
        ], 200); // 200 ok
    }

    public function getByNombre(string $nombre)
    {
        $cliente = Cliente::where('nombre', 'like', '%' . $nombre . '%')->get();

        if (!$cliente) {
            return response()->json(['message' => 'Ninguna coincidencia encontrada'], 404);
        }

        return response()->json([
            'message' => 'Clientes Encontrados',
            'data' => $cliente
        ], 200); // 200 ok
    }
}
