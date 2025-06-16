<?php

namespace App\Http\Controllers\Crud_basic\Users;

use App\Http\Controllers\Controller;
use App\Services\RecepcionistaLogic; // Asegúrate que sea App\Services\RecepcionistaLogic
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\File; // Importa la fachada File

class RecepcionistaController extends Controller
{
    protected $recepcionistaLogic;

    public function __construct(RecepcionistaLogic $recepcionistaLogic)
    {
        $this->recepcionistaLogic = $recepcionistaLogic;
    }

    public function register(Request $request)
    {
        try {
            $request->validate([
                'cedula' => 'required|string|unique:recepcionista|max:20',
                'nombre' => 'required|string|max:255',
                'email' => 'required|string|email|unique:recepcionista|max:255',
                'password' => 'required|string|min:5',
                'edad' => 'nullable|int|max:20',
                'sexo' => 'nullable|string|max:255',
                'salario' => 'required|numeric',
                'urlImage' => 'required|image|mimes:jpeg,png,jpg,gif,svg|max:2048' // Imagen requerida al registrar
            ]);

            $imagePath = null;
            if ($request->hasFile('urlImage')) {
                $image = $request->file('urlImage');
                $cedula = $request->input('cedula');
                $imageName = $cedula . '.' . $image->getClientOriginalExtension();
                $destinationPath = public_path('images/recepcionistas');

                if (!File::isDirectory($destinationPath)) {
                    File::makeDirectory($destinationPath, 0777, true, true);
                }

                $image->move($destinationPath, $imageName);
                $imagePath = 'images/recepcionistas/' . $imageName;
            }

        } catch (ValidationException $e) {
            return response()->json([
                'message' => 'Error de validación',
                'errors' => $e->errors()
            ], 422);
        }

        return $this->recepcionistaLogic->registrar(
            $request->cedula,
            $request->nombre,
            $request->email,
            $request->password,
            $request->edad,
            $request->sexo,
            $request->salario,
            $imagePath // Pasa la ruta de la imagen
        );
    }

    public function login(Request $request)
    {
        try {
            $request->validate([
                'email' => 'required|string|email',
                'password' => 'required|string'
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'message' => 'Error de validación',
                'errors' => $e->errors()
            ], 422);
        }
        return $this->recepcionistaLogic->login($request->email, $request->password);
    }

    public function logout(Request $request)
    {
        return $this->recepcionistaLogic->logout($request->user('recepcionista_api'));
    }

    public function user(Request $request)
    {
        return $this->recepcionistaLogic->getAutenticado($request->user('recepcionista_api'));
    }

    public function userAuth(Request $request)
    {
        return $this->recepcionistaLogic->getAutenticado($request->user('recepcionista_api'));
    }

    public function getAll()
    {
        return $this->recepcionistaLogic->getAll();
    }

    public function getByCedula(Request $request)
    {
        return $this->recepcionistaLogic->getByCedula($request->cedula);
    }

    public function getByEmail(Request $request)
    {
        return $this->recepcionistaLogic->getByEmail($request->email);
    }

    public function getByNombre(Request $request)
    {
        return $this->recepcionistaLogic->getByNombre($request->nombre);
    }

    public function update(Request $request)
    {
        try {
            $request->validate([
                'cedula' => 'required|string|max:20',
                'nombre' => 'required|string|max:255',
                'email' => 'required|string|email|max:255',
                'edad' => 'nullable|int|max:20',
                'sexo' => 'nullable|string|max:255',
                'salario' => 'nullable|numeric|min:0',
                'urlImage' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048' // Imagen opcional al actualizar
            ]);

            $imagePath = null;
            $cedula = $request->input('cedula');

            $existingRecepcionista = $this->recepcionistaLogic->getByCedula($cedula);
            if ($existingRecepcionista && property_exists($existingRecepcionista, 'urlImage')) {
                $imagePath = $existingRecepcionista->urlImage;
            }

            if ($request->hasFile('urlImage')) {
                $image = $request->file('urlImage');
                
                if ($existingRecepcionista && $existingRecepcionista->urlImage) {
                    $oldImagePath = public_path($existingRecepcionista->urlImage);
                    if (File::exists($oldImagePath)) {
                        File::delete($oldImagePath);
                    }
                }
                
                $imageName = $cedula . '.' . $image->getClientOriginalExtension();
                $destinationPath = public_path('images/recepcionistas');

                if (!File::isDirectory($destinationPath)) {
                    File::makeDirectory($destinationPath, 0777, true, true);
                }

                $image->move($destinationPath, $imageName);
                $imagePath = 'images/recepcionistas/' . $imageName;
            }

        } catch (ValidationException $e) {
            return response()->json([
                'message' => 'Error de validación',
                'errors' => $e->errors()
            ], 422);
        }

        $array = [
            'cedula' => $request->cedula,
            'nombre' => $request->nombre,
            'email' => $request->email,
            'edad' => $request->edad,
            'sexo' => $request->sexo,
            'salario' => $request->salario,
            'urlImage' => $imagePath // Pasa la ruta de la imagen
        ];
        
        return $this->recepcionistaLogic->update($request->cedula, $array);
    }

    public function delete(Request $request)
    {
        $existingRecepcionista = $this->recepcionistaLogic->getByCedula($request->cedula);
        if ($existingRecepcionista && property_exists($existingRecepcionista, 'urlImage') && $existingRecepcionista->urlImage) {
            $imagePathToDelete = public_path($existingRecepcionista->urlImage);
            if (File::exists($imagePathToDelete)) {
                File::delete($imagePathToDelete);
            }
        }
        return $this->recepcionistaLogic->delete($request->cedula);
    }

        // --- Métodos de Gestión del Propio Perfil del Recepcionista ---

    /**
     * Obtener el perfil del recepcionista autenticado.
     */
    public function getAuthRecepcionistaProfile()
    {
        $user = Auth::guard('recepcionista_api')->user();
        if (!$user) {
            return response()->json(['message' => 'Usuario no autenticado'], 401);
        }
        return $this->recepcionistaLogic->getByCedula($user->cedula);
    }

    /**
     * Actualizar el perfil del recepcionista autenticado.
     */
    public function updateAuthRecepcionistaProfile(Request $request)
    {
        $user = Auth::guard('recepcionista_api')->user();
        if (!$user) {
            return response()->json(['message' => 'Usuario no autenticado'], 401);
        }

        try {
            $request->validate([
                'nombre' => 'required|string|max:100',
                'email' => 'required|email|max:255|unique:recepcionista,email,' . $user->cedula . ',cedula', // Ignorar el propio email
                'password' => 'nullable|string|min:8|confirmed', // Contraseña opcional
                'edad' => 'nullable|integer|min:18|max:100', // Nullable si no es estrictamente necesario
                'sexo' => 'nullable|string|in:M,F',          // Nullable
                'salario' => 'nullable|numeric|min:0',      // Nullable
                'urlImage' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048', // Imagen opcional
            ]);

            $data = $request->only(['nombre', 'email', 'password', 'edad', 'sexo', 'salario']);

            // Manejo de la imagen: similar a ClienteController
            $imagePath = $user->urlImage;
            if ($request->hasFile('urlImage')) {
                $image = $request->file('urlImage');
                $imageName = $user->cedula . '.' . $image->getClientOriginalExtension();
                $destinationPath = public_path('images/recepcionistas'); // Directorio para imágenes de recepcionistas

                // Eliminar la imagen antigua si existe
                if ($user->urlImage && File::exists(public_path($user->urlImage))) {
                    File::delete(public_path($user->urlImage));
                }
                
                if (!File::isDirectory($destinationPath)) {
                    File::makeDirectory($destinationPath, 0777, true, true);
                }
                $image->move($destinationPath, $imageName);
                $imagePath = 'images/recepcionistas/' . $imageName;
            } else if ($request->input('removeImage')) { // Si se indica explícitamente eliminar la imagen
                if ($user->urlImage && File::exists(public_path($user->urlImage))) {
                    File::delete(public_path($user->urlImage));
                }
                $imagePath = null;
            }
            $data['urlImage'] = $imagePath;

            return $this->recepcionistaLogic->update($user->cedula, $data);

        } catch (ValidationException $e) {
            return response()->json(['message' => 'Error de validación', 'errors' => $e->errors()], 422);
        }
    }

    /**
     * Eliminar la cuenta del recepcionista autenticado.
     */
    public function deleteAuthRecepcionistaAccount(Request $request)
    {
        $user = Auth::guard('recepcionista_api')->user();
        if (!$user) {
            return response()->json(['message' => 'Usuario no autenticado'], 401);
        }
        return $this->recepcionistaLogic->delete($user->cedula);
    }
}