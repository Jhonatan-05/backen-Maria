<?php

namespace App\Http\Controllers\Crud_basic\Elements; // Nota: el namespace ha cambiado a Elements

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\productoLogic; // Asegúrate que sea App\Services\productoLogic
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\File; // Importa la fachada File

class ProductoController extends Controller
{
    protected $productoLogic;

    public function __construct(productoLogic $productoLogic)
    {
        $this->productoLogic = $productoLogic;
    }

    public function getAll()
    {
        $productos = $this->productoLogic->getAll();
        return $productos;
    }

    public function getByCodigo(Request $request)
    {
        return $this->productoLogic->getByCodigo($request->codigo);
    }

    public function create(Request $request)
    {
        try {
            $request->validate([
                'codigo' => 'required|string|max:20|unique:producto',
                'nombre' => 'required|string|max:255',
                'descripcion' => 'nullable|string|max:500',
                'precio' => 'required|numeric|min:0',
                'stock' => 'required|integer|min:0',
                'urlImage' => 'required|image|mimes:jpeg,png,jpg,gif,svg|max:2048' // Imagen requerida al crear
            ]);

            $imagePath = null;
            if ($request->hasFile('urlImage')) {
                $image = $request->file('urlImage');
                $codigo = $request->input('codigo');
                $imageName = $codigo . '.' . $image->getClientOriginalExtension();
                $destinationPath = public_path('images/productos');

                if (!File::isDirectory($destinationPath)) {
                    File::makeDirectory($destinationPath, 0777, true, true);
                }

                $image->move($destinationPath, $imageName);
                $imagePath = 'images/productos/' . $imageName;
            }

        } catch (ValidationException $e) {
            return response()->json(['message' => 'Error de validación', 'errors' => $e->errors()], 422);
        }

        return $this->productoLogic->create(
            $request->codigo,
            $request->nombre,
            $request->descripcion,
            $request->precio,
            $request->stock,
            $imagePath // Pasa la ruta de la imagen
        );
    }

    public function update(Request $request)
    {
        try {
            $request->validate([
                'codigo' => 'required|string|max:20|exists:servicio,codigo', // Posiblemente debería ser 'exists:producto,codigo' aquí.
                'nombre' => 'required|string|max:255',
                'descripcion' => 'nullable|string|max:500',
                'precio' => 'required|numeric|min:0',
                'stock' => 'required|integer|min:0',
                'urlImage' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048' // Imagen opcional al actualizar
            ]);

            $imagePath = null;
            $codigo = $request->input('codigo');

            $existingProducto = $this->productoLogic->getByCodigo($codigo);
            if ($existingProducto && property_exists($existingProducto, 'urlImage')) {
                $imagePath = $existingProducto->urlImage;
            }

            if ($request->hasFile('urlImage')) {
                $image = $request->file('urlImage');
                
                if ($existingProducto && $existingProducto->urlImage) {
                    $oldImagePath = public_path($existingProducto->urlImage);
                    if (File::exists($oldImagePath)) {
                        File::delete($oldImagePath);
                    }
                }
                
                $imageName = $codigo . '.' . $image->getClientOriginalExtension();
                $destinationPath = public_path('images/productos');

                if (!File::isDirectory($destinationPath)) {
                    File::makeDirectory($destinationPath, 0777, true, true);
                }

                $image->move($destinationPath, $imageName);
                $imagePath = 'images/productos/' . $imageName;
            }

        } catch (ValidationException $e) {
            return response()->json(['message' => 'Error de validación', 'errors' => $e->errors()], 422);
        }

        return $this->productoLogic->update(
            $request->codigo,
            $request->nombre,
            $request->descripcion,
            $request->precio,
            $request->stock,
            $imagePath // Pasa la ruta de la imagen
        );
    }

    public function destroy(Request $request)
    {
        $existingProducto = $this->productoLogic->getByCodigo($request->codigo);
        if ($existingProducto && property_exists($existingProducto, 'urlImage') && $existingProducto->urlImage) {
            $imagePathToDelete = public_path($existingProducto->urlImage);
            if (File::exists($imagePathToDelete)) {
                File::delete($imagePathToDelete);
            }
        }
        return $this->productoLogic->delete($request->codigo);
    }
}