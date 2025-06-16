<?php

namespace App\Services;

use App\Models\Producto;

class ProductoLogic
{
    /**
     * Obtener todos los productos.
     */
    public static function getAll()
    {
        $productos = Producto::all();
        return response()->json([
            'message' => 'Lista de productos',
            'data' => $productos->toArray()
        ], 200);
    }

    /**
     * Obtener un producto por su código.
     */
    public static function getByCodigo($codigo)
    {
        $producto = Producto::find($codigo);
        if (!$producto) {
            return response()->json(['message' => 'Producto no encontrado'], 404);
        }
        return response()->json([
            'message' => 'Producto encontrado',
            'data' => $producto
        ], 200);
    }

    /**
     * Crear un nuevo producto.
     */
    public static function create($codigo, $nombre, $descripcion, $precio, $stock, $urlImage = null)
    {
        $producto = Producto::create([
            'codigo' => $codigo,
            'nombre' => $nombre,
            'descripcion' => $descripcion,
            'precio' => $precio,
            'stock' => $stock,
            'urlImage' => $urlImage
        ]);

        return response()->json([
            'message' => 'Producto creado exitosamente',
            'data' => $producto
        ], 201);
    }

    /**
     * Actualizar un producto existente.
     */
    public static function update($codigo, $nombre, $descripcion, $precio, $stock, $urlImage = null)
    {
        $producto = Producto::find($codigo);
        if (!$producto) {
            return response()->json(['message' => 'Producto no encontrado'], 404);
        }

        $producto->nombre = $nombre;
        $producto->descripcion = $descripcion;
        $producto->precio = $precio;
        $producto->stock = $stock;
        if ($urlImage !== null) {
            $producto->urlImage = $urlImage;
        }
        $producto->save();

        return response()->json([
            'message' => 'Producto actualizado correctamente',
            'data' => $producto
        ], 200);
    }

    /**
     * Eliminar un producto por su código.
     */
    public static function delete($codigo)
    {
        $producto = Producto::find($codigo);
        if (!$producto) {
            return response()->json(['message' => 'Producto no encontrado'], 404);
        }
        $producto->delete();
        return response()->json(['message' => 'Producto eliminado correctamente'], 200);
    }
}