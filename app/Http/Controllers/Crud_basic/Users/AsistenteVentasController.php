<?php

namespace App\Http\Controllers\Crud_basic\Users;

use App\Services\AsistenteVentasLogic; // Importa la lógica de asistente de ventas
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\File; // Importa la fachada File para manejar archivos

class AsistenteVentasController extends Controller
{
    protected $asistenteVentasLogic;

    public function __construct(AsistenteVentasLogic $asistenteVentasLogic)
    {
        $this->asistenteVentasLogic = $asistenteVentasLogic;
    }

    /**
     * Registra un nuevo asistente de ventas.
     */
    public function register(Request $request)
    {
        try {
            // Validación de los datos de entrada, incluyendo la imagen como requerida
            $request->validate([
                'cedula' => 'required|string|max:20|unique:asistenteventas,cedula',
                'nombre' => 'required|string|max:100',
                'email' => 'required|email|max:255|unique:asistenteventas,email',
                'password' => 'required|string|min:8|confirmed',
                'edad' => 'required|integer|min:18|max:100',
                'sexo' => 'required|string|in:M,F',
                'salario' => 'required|numeric|min:0',
                'urlImage' => 'required|image|mimes:jpeg,png,jpg,gif,svg|max:2048' // Imagen requerida al registrar
            ]);

            $imagePath = null;
            // Verifica si se ha subido un archivo con el nombre 'urlImage'
            if ($request->hasFile('urlImage')) {
                $image = $request->file('urlImage');
                $cedula = $request->input('cedula'); // Obtiene la cédula para usarla como nombre del archivo

                // Genera el nombre de la imagen usando la cédula y la extensión original
                $imageName = $cedula . '.' . $image->getClientOriginalExtension();
                
                // Define la ruta donde se guardará la imagen dentro de la carpeta 'public'
                $destinationPath = public_path('images/asistentes');

                // Asegura que el directorio exista. Si no, lo crea.
                if (!File::isDirectory($destinationPath)) {
                    File::makeDirectory($destinationPath, 0777, true, true);
                }

                // Mueve la imagen a la carpeta destino
                $image->move($destinationPath, $imageName);
                
                // Guarda la ruta relativa para almacenar en la base de datos
                $imagePath = 'images/asistentes/' . $imageName;
            }

        } catch (ValidationException $e) {
            // Retorna errores de validación
            return response()->json(['errors' => $e->errors()], 422);
        }

        // Llama a la lógica de negocio para registrar el asistente con la ruta de la imagen
        return $this->asistenteVentasLogic->registrar(
            $request->cedula,
            $request->nombre,
            $request->email,
            $request->password,
            $request->edad,
            $request->sexo,
            $request->salario,
            $imagePath // Pasa la ruta de la imagen al servicio
        );
    }

    /**
     * Inicia sesión para un asistente de ventas.
     */
    public function login(Request $request)
    {
        // Validación de los campos de inicio de sesión
        try {
            $request->validate([
                'email' => 'required|string|email',
                'password' => 'required|string',
            ]);
        } catch (ValidationException $e) {
            return response()->json(['errors' => $e->errors()], 422);
        }
        return $this->asistenteVentasLogic->login($request->email, $request->password);
    }

    /**
     * Cierra la sesión del asistente de ventas (revoca el token actual).
     */
    public function logout(Request $request)
    {
        return $this->asistenteVentasLogic->logout($request->user('asistente_api'));
    }

    /**
     * Obtiene los datos del asistente de ventas autenticado.
     */
    public function user(Request $request)
    {
        return $this->asistenteVentasLogic->getAutenticado($request->user('asistente_api'));
    }

    // Mostrar todos asistentes de ventas
    public function getAll()
    {
        return $this->asistenteVentasLogic->getAll();
    }

    // Mostrar un asistente de ventas por cédula
    public function getByCedula($id)
    {
        return $this->asistenteVentasLogic->getByCedula($id);
    }

    // Crear un nuevo asistente de ventas (este método es redundante si 'register' ya existe y maneja la creación completa)
    // Se recomienda usar el método 'register' para la creación. Si se mantiene 'create',
    // debería llamar a 'register' o eliminar el código duplicado.
    public function create(Request $request)
    {
        // Idealmente, este método debería llamar a 'register' o ser refactorizado.
        // Por ahora, se mantendrá la llamada a registrar sin la imagen,
        // pero se recomienda usar 'register' para nuevas creaciones con imagen.
        return $this->asistenteVentasLogic->registrar(
            $request->cedula,
            $request->nombre,
            $request->email,
            $request->password,
            $request->edad,
            $request->sexo,
            $request->salario,
            null // No se maneja la imagen aquí, se asume que 'register' es para eso.
        );
    }

    // Actualizar un asistente de ventas existente
    public function update(Request $request)
    {
        try {
            // Validación de los datos de entrada, incluyendo la imagen como opcional
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

            // Obtiene el asistente existente para verificar si ya tiene una imagen
            $existingAsistente = $this->asistenteVentasLogic->getByCedula($cedula);
            if ($existingAsistente && property_exists($existingAsistente, 'urlImage')) {
                $imagePath = $existingAsistente->urlImage; // Por defecto, mantiene la imagen existente
            }

            // Si se ha subido una nueva imagen
            if ($request->hasFile('urlImage')) {
                $image = $request->file('urlImage');
                
                // Si existe una imagen antigua, la elimina
                if ($existingAsistente && $existingAsistente->urlImage) {
                    $oldImagePath = public_path($existingAsistente->urlImage);
                    if (File::exists($oldImagePath)) {
                        File::delete($oldImagePath); // Elimina el archivo antiguo
                    }
                }
                
                // Genera el nombre de la nueva imagen
                $imageName = $cedula . '.' . $image->getClientOriginalExtension();
                $destinationPath = public_path('images/asistentes');

                // Asegura que el directorio exista
                if (!File::isDirectory($destinationPath)) {
                    File::makeDirectory($destinationPath, 0777, true, true);
                }

                // Mueve la nueva imagen a la carpeta destino
                $image->move($destinationPath, $imageName);
                $imagePath = 'images/asistentes/' . $imageName; // Actualiza la ruta de la imagen
            }

        } catch (ValidationException $e) {
            // Retorna errores de validación
            return response()->json([
                'message' => 'Error de validación',
                'errors' => $e->errors()
            ], 422); // Código 422 para errores de validación
        }
        
        // Prepara el array con los datos a actualizar, incluyendo la ruta de la imagen
        $array = [
            'cedula' => $request->cedula,
            'nombre' => $request->nombre,
            'email' => $request->email,
            'edad' => $request->edad,
            'sexo' => $request->sexo,
            'salario' => $request->salario,
            'urlImage' => $imagePath // Pasa la ruta de la imagen (nueva o existente)
        ];

        return $this->asistenteVentasLogic->update($request->cedula, $array);
    }

    // Eliminar un asistente de ventas
    public function delete(Request $request)
    {
        // Obtiene el asistente antes de eliminarlo para poder acceder a la ruta de la imagen
        $existingAsistente = $this->asistenteVentasLogic->getByCedula($request->cedula);
        
        // Si el asistente existe y tiene una imagen asociada, la elimina del servidor
        if ($existingAsistente && property_exists($existingAsistente, 'urlImage') && $existingAsistente->urlImage) {
            $imagePathToDelete = public_path($existingAsistente->urlImage);
            if (File::exists($imagePathToDelete)) {
                File::delete($imagePathToDelete); // Elimina el archivo de imagen
            }
        }
        // Llama a la lógica de negocio para eliminar el asistente de la base de datos
        return $this->asistenteVentasLogic->delete($request->cedula);
    }

    public function getByEmail(Request $request)
    {
        return $this->asistenteVentasLogic->getByEmail($request->email);
    }

    public function getByNombre(Request $request)
    {
        return $this->asistenteVentasLogic->getByNombre($request->nombre);
    }
}