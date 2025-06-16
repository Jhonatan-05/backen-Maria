<?php

namespace App\Http\Controllers\Crud_basic\Users;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use App\Services\ClienteLogic;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\File; // Importa la fachada File para manejar archivos

class ClienteController extends Controller
{
    protected $clienteLogic;

    public function __construct(ClienteLogic $clienteLogic)
    {
        $this->clienteLogic = $clienteLogic;
    }

    /**
     * Registra un nuevo cliente.
     */
    public function register(Request $request)
    {
        try {
            // Valida los datos de la solicitud, incluyendo la imagen
            $request->validate([
                'cedula' => 'required|string|unique:cliente|max:20',
                'nombre' => 'required|string|max:255',
                'email' => 'required|string|email|unique:cliente|max:255',
                'password' => 'required|string|min:5',
                'edad' => 'required|int|max:20',
                'sexo' => 'required|string|max:255',
                'urlImage' => 'image|mimes:jpeg,png,jpg,gif,svg|max:2048', 
            ]);

            $imagePath = null;
            // Verifica si se ha subido un archivo con el nombre 'urlImage'
            if ($request->hasFile('urlImage')) {
                $image = $request->file('urlImage');
                $cedula = $request->input('cedula'); // Obtiene la cédula para usarla como nombre del archivo

                // Genera el nombre de la imagen usando la cédula y la extensión original
                $imageName = $cedula . '.' . $image->getClientOriginalExtension();
                
                // Define la ruta donde se guardará la imagen dentro de la carpeta 'public'
                $destinationPath = public_path('images/clientes');

                // Asegura que el directorio exista. Si no, lo crea.
                if (!File::isDirectory($destinationPath)) {
                    File::makeDirectory($destinationPath, 0777, true, true);
                }

                // Mueve la imagen a la carpeta destino
                $image->move($destinationPath, $imageName);
                
                // Guarda la ruta relativa para almacenar en la base de datos
                $imagePath = 'images/clientes/' . $imageName;
            }

        } catch (ValidationException $e) {
            // Retorna errores de validación
            return response()->json([
                'message' => 'Error de validación',
                'errors' => $e->errors()
            ], 422); // Código 422 para errores de validación
        }

        // Llama a la lógica de negocio para registrar el cliente con la ruta de la imagen
        return $this->clienteLogic->registrar(
            $request->cedula,
            $request->nombre,
            $request->email,
            $request->password,
            $request->edad,
            $request->sexo,
            $imagePath // Pasa la ruta de la imagen al servicio
        );
    }
    
    /**
     * Inicia sesión para un cliente.
     */
    public function login(Request $request)
    {
        try {
            // Validación de los campos de inicio de sesión
            $request->validate(['email' => 'required|string|email', 'password' => 'required|string']);
        } catch (ValidationException $e) {
            return response()->json([
                'message' => 'Error de validación',
                'errors' => $e->errors()
            ], 422); // Código 422 para errores de validación
        }
        return $this->clienteLogic->login($request->email, $request->password);
    }

    /**
     * Cierra la sesión del cliente (revoca el token actual).
     */
    public function logout(Request $request)
    {
        return $this->clienteLogic->logout($request->user('cliente_api'));
    }

    /**
     * Obtiene los datos del cliente autenticado.
     */
    public function userAuth(Request $request)
    {
        // Retorna el usuario autenticado bajo el guard 'cliente_api'
        return $this->clienteLogic->getAutenticado($request->user('cliente_api'));
    }

    // Mostrar todos los clientes
    public function getAll()
    {
        return $this->clienteLogic->getAll();
    }

    // Mostrar un cliente específico
    public function getByCedula(Request $request)
    {
        return $this->clienteLogic->getByCedula($request->cedula);
    }

    public function getByEmail(Request $request)
    {
        return $this->clienteLogic->getByEmail($request->email);
    }

    public function getByNombre(Request $request)
    {
        return $this->clienteLogic->getByNombre($request->nombre);
    }

    // Actualizar un cliente existente
    public function update(Request $request)
    {
        try {
            // Valida los datos de la solicitud, incluyendo la imagen (ahora es opcional)
            $request->validate([
                'cedula' => 'required|string|max:20',
                'nombre' => 'required|string|max:255',
                'email' => 'required|string|email|max:255',
                'edad' => 'nullable|int|max:20',
                'sexo' => 'nullable|string|max:255',
                'urlImage' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048', // La imagen es opcional al actualizar
            ]);

            $imagePath = null;
            $cedula = $request->input('cedula');

            // Obtiene el cliente existente para verificar si ya tiene una imagen
            $existingClient = $this->clienteLogic->getByCedula($cedula);
            if ($existingClient && property_exists($existingClient, 'urlImage')) {
                $imagePath = $existingClient->urlImage; // Por defecto, mantiene la imagen existente
            }

            // Si se ha subido una nueva imagen
            if ($request->hasFile('urlImage')) {
                $image = $request->file('urlImage');
                
                // Si existe una imagen antigua, la elimina
                if ($existingClient && $existingClient->urlImage) {
                    $oldImagePath = public_path($existingClient->urlImage);
                    if (File::exists($oldImagePath)) {
                        File::delete($oldImagePath); // Elimina el archivo antiguo
                    }
                }
                
                // Genera el nombre de la nueva imagen
                $imageName = $cedula . '.' . $image->getClientOriginalExtension();
                $destinationPath = public_path('images/clientes');

                // Asegura que el directorio exista
                if (!File::isDirectory($destinationPath)) {
                    File::makeDirectory($destinationPath, 0777, true, true);
                }

                // Mueve la nueva imagen a la carpeta destino
                $image->move($destinationPath, $imageName);
                $imagePath = 'images/clientes/' . $imageName; // Actualiza la ruta de la imagen
            }

        } catch (ValidationException $e) {
            // Retorna errores de validación
            return response()->json([
                'message' => 'Error de validación',
                'errors' => $e->errors()
            ], 422); // Código 422 para errores de validación
        }
        
        // Llama a la lógica de negocio para actualizar el cliente con la ruta de la imagen
        return $this->clienteLogic->update( 
            $request->cedula,
            $request->nombre,
            $request->email,
            $request->edad,
            $request->sexo,
            $imagePath // Pasa la ruta de la imagen (nueva o existente)
        );
    }

    /**
     * NUEVO MÉTODO: Obtener el perfil del cliente autenticado.
     */
    public function getAuthClientProfile()
    {
        $user = Auth::guard('cliente_api')->user();
        if (!$user) {
            return response()->json(['message' => 'Usuario no autenticado'], 401);
        }
        return $this->clienteLogic->getByCedula($user->cedula);
    }

    /**
     * NUEVO MÉTODO: Actualizar el perfil del cliente autenticado.
     */
    public function updateAuthClientProfile(Request $request)
    {
        $user = Auth::guard('cliente_api')->user();
        if (!$user) {
            return response()->json(['message' => 'Usuario no autenticado'], 401);
        }

        try {
            $request->validate([
                'nombre' => 'required|string|max:100',
                'email' => 'required|email|max:255|unique:cliente,email,' . $user->cedula . ',cedula', 
                'password' => 'nullable|string|min:8|confirmed', // Contraseña opcional al actualizar
                'edad' => 'required|integer|min:18|max:100',
                'sexo' => 'required|string|in:M,F',
                'urlImage' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048', // Imagen opcional al actualizar
            ]);

            $data = $request->only(['nombre', 'email', 'password', 'edad', 'sexo']);

            // Manejo de la imagen: si se envía una nueva, se guarda y se elimina la antigua.
            $imagePath = $user->urlImage; // Mantener la imagen existente por defecto
            if ($request->hasFile('urlImage')) {
                $image = $request->file('urlImage');
                $imageName = $user->cedula . '.' . $image->getClientOriginalExtension();
                $destinationPath = public_path('images/clientes');

                // Eliminar la imagen antigua si existe
                if ($user->urlImage && File::exists(public_path($user->urlImage))) {
                    File::delete(public_path($user->urlImage));
                }
                
                if (!File::isDirectory($destinationPath)) {
                    File::makeDirectory($destinationPath, 0777, true, true);
                }
                $image->move($destinationPath, $imageName);
                $imagePath = 'images/clientes/' . $imageName;
            } else if ($request->input('removeImage')) { // Si se indica explícitamente eliminar la imagen
                if ($user->urlImage && File::exists(public_path($user->urlImage))) {
                    File::delete(public_path($user->urlImage));
                }
                $imagePath = null;
            }
            $data['urlImage'] = $imagePath;

            return $this->clienteLogic->update($user->cedula, $data);

        } catch (ValidationException $e) {
            return response()->json(['message' => 'Error de validación', 'errors' => $e->errors()], 422);
        }
    }

    /**
     * NUEVO MÉTODO: Eliminar la cuenta del cliente autenticado.
     */
    public function deleteAuthClientAccount(Request $request)
    {
        $user = Auth::guard('cliente_api')->user();
        if (!$user) {
            return response()->json(['message' => 'Usuario no autenticado'], 401);
        }

        // Llama a la lógica de eliminación para el cliente autenticado
        return $this->clienteLogic->delete($user->cedula);
    }

    // Eliminar un cliente
    public function delete(Request $request)
    {
        // Obtiene el cliente antes de eliminarlo para poder acceder a la ruta de la imagen
        $existingClient = $this->clienteLogic->getByCedula($request->cedula);
        
        // Si el cliente existe y tiene una imagen asociada, la elimina del servidor
        if ($existingClient && property_exists($existingClient, 'urlImage') && $existingClient->urlImage) {
            $imagePathToDelete = public_path($existingClient->urlImage);
            if (File::exists($imagePathToDelete)) {
                File::delete($imagePathToDelete); // Elimina el archivo de imagen
            }
        }
        // Llama a la lógica de negocio para eliminar el cliente de la base de datos
        return $this->clienteLogic->delete($request->cedula);
    }
}