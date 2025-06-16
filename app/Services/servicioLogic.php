<?php

namespace App\Services;

use App\Models\Servicio;

class ServicioLogic
{
    /**
     * Obtener todos los servicios.
     */
    public static function getAll()
    {
        $servicios = Servicio::all();
        return response()->json([
            'message' => 'Lista de servicios',
            'data' => $servicios->toArray() // <-- CAMBIO: Convertir a array
        ], 200);
    }

    // ... resto de los métodos
    /**
     * Obtener un servicio por su código.
     */
    public static function getByCodigo($codigo)
    {
        $servicio = Servicio::find($codigo);
        if (!$servicio) {
            return response()->json(['message' => 'Servicio no encontrado'], 404);
        }
        return response()->json([
            'message' => 'Servicio encontrado',
            'data' => $servicio->toArray() // <-- CAMBIO: Convertir a array
        ], 200);
    }

    /**
     * Crear un nuevo servicio.
     */
    public static function create($codigo, $nombre, $descripcion, $precio, $urlImage = null)
    {
        $servicio = Servicio::create([
            'codigo' => $codigo,
            'nombre' => $nombre,
            'descripcion' => $descripcion,
            'precio' => $precio,
            'urlImage' => $urlImage
        ]);

        return response()->json([
            'message' => 'Servicio creado exitosamente',
            'data' => $servicio->toArray() // <-- CAMBIO: Convertir a array
        ], 201);
    }

    /**
     * Actualizar un servicio existente.
     */
    public static function update($codigo, $nombre, $descripcion, $precio, $urlImage = null)
    {
        $servicio = Servicio::find($codigo);
        if (!$servicio) {
            return response()->json(['message' => 'Servicio no encontrado'], 404);
        }
        $servicio->nombre = $nombre;
        $servicio->descripcion = $descripcion;
        $servicio->precio = $precio;
        if ($urlImage !== null) {
            $servicio->urlImage = $urlImage;
        }
        $servicio->save();

        return response()->json([
            'message' => 'Servicio actualizado correctamente',
            'data' => $servicio->toArray() // <-- CAMBIO: Convertir a array
        ], 200);
    }

    /**
     * Eliminar un servicio por su código.
     */
    public static function delete($codigo)
    {
        $servicio = Servicio::find($codigo);
        if (!$servicio) {
            return response()->json(['message' => 'Servicio no encontrado'], 404);
        }
        $servicio->delete();
        return response()->json(['message' => 'Servicio eliminado correctamente'], 200);
    }
}
