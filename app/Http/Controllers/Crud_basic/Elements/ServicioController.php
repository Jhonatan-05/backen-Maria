<?php

namespace App\Http\Controllers\Crud_basic\Elements; // Nota: el namespace ha cambiado a Elements

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\servicioLogic; // Asegúrate que sea App\Services\servicioLogic
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\File; // Importa la fachada File

class ServicioController extends Controller
{
    protected $servicioLogic;

    public function __construct(servicioLogic $servicioLogic)
    {
        $this->servicioLogic = $servicioLogic;
    }

    public function getAll()
    {
        return $this->servicioLogic->getAll();
    }

    public function getByCodigo(Request $request)
    {
        return $this->servicioLogic->getByCodigo($request->codigo);
    }

    public function create(Request $request)
    {
        try {
            $request->validate([
                'codigo' => 'required|string|unique:servicio',
                'nombre' => 'required|string|max:255',
                'descripcion' => 'nullable|string|max:500',
                'precio' => 'required|numeric|min:0',
                'urlImage' => 'required|image|mimes:jpeg,png,jpg,gif,svg|max:2048' // Imagen requerida al crear
            ]);

            $imagePath = null;
            if ($request->hasFile('urlImage')) {
                $image = $request->file('urlImage');
                $codigo = $request->input('codigo');
                $imageName = $codigo . '.' . $image->getClientOriginalExtension();
                $destinationPath = public_path('images/servicios');

                if (!File::isDirectory($destinationPath)) {
                    File::makeDirectory($destinationPath, 0777, true, true);
                }

                $image->move($destinationPath, $imageName);
                $imagePath = 'images/servicios/' . $imageName;
            }

        } catch (ValidationException $e) {
            return response()->json(['message' => 'Error de validación', 'errors' => $e->errors()], 422);
        }

        return $this->servicioLogic->create(
            $request->codigo,
            $request->nombre,
            $request->descripcion,
            $request->precio,
            $imagePath // Pasa la ruta de la imagen
        );
    }

    public function update(Request $request)
    {
        try {
            $request->validate([
                'codigo' => 'required|string|exists:servicio,codigo',
                'nombre' => 'required|string|max:255',
                'descripcion' => 'nullable|string|max:500',
                'precio' => 'required|numeric|min:0',
                'urlImage' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048' // Imagen opcional al actualizar
            ]);

            $imagePath = null;
            $codigo = $request->input('codigo');

            $existingServicio = $this->servicioLogic->getByCodigo($codigo);
            if ($existingServicio && property_exists($existingServicio, 'urlImage')) {
                $imagePath = $existingServicio->urlImage;
            }

            if ($request->hasFile('urlImage')) {
                $image = $request->file('urlImage');
                
                if ($existingServicio && $existingServicio->urlImage) {
                    $oldImagePath = public_path($existingServicio->urlImage);
                    if (File::exists($oldImagePath)) {
                        File::delete($oldImagePath);
                    }
                }
                
                $imageName = $codigo . '.' . $image->getClientOriginalExtension();
                $destinationPath = public_path('images/servicios');

                if (!File::isDirectory($destinationPath)) {
                    File::makeDirectory($destinationPath, 0777, true, true);
                }

                $image->move($destinationPath, $imageName);
                $imagePath = 'images/servicios/' . $imageName;
            }

        } catch (ValidationException $e) {
            return response()->json(['message' => 'Error de validación', 'errors' => $e->errors()], 422);
        }

        return $this->servicioLogic->update(
            $request->codigo,
            $request->nombre,
            $request->descripcion,
            $request->precio,
            $imagePath // Pasa la ruta de la imagen
        );
    }

    public function destroy(Request $request)
    {
        $existingServicio = $this->servicioLogic->getByCodigo($request->codigo);
        if ($existingServicio && property_exists($existingServicio, 'urlImage') && $existingServicio->urlImage) {
            $imagePathToDelete = public_path($existingServicio->urlImage);
            if (File::exists($imagePathToDelete)) {
                File::delete($imagePathToDelete);
            }
        }
        return $this->servicioLogic->delete($request->codigo);
    }
}