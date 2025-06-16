<?php

namespace App;

use App\Models\Informe;

class InformeLogic
{
    /**
     * Crear un nuevo informe.
     */
    public static function create($codigo, $idEspecialista, $nombre, $descripcion, $codigoCita)

    {
        if (Informe::where('codigo', $codigo)->exists()) {
            return response()->json(['message' => 'El informe ya existe'], 400);
        }
        $informe = Informe::create([
            'codigo' => $codigo,
            'idEspecialista' => $idEspecialista,
            'nombre' => $nombre,
            'descripción' => $descripcion,
            'codigoCita' => $codigoCita
        ]);

        return response()->json([
            'message' => 'Informe creado exitosamente',
            'data' => $informe
        ], 201);
    }

    /**
     * Actualizar un informe existente.
     */
    public static function update($codigo, $idEspecialista, $nombre, $descripcion, $codigoCita)
    {
        $informe = Informe::find($codigo);
        if ($informe) {
            $informe->idEspecialista = $idEspecialista;
            $informe->nombre = $nombre;
            $informe->descripción = $descripcion;
            $informe->codigoCita = $codigoCita;
            $informe->save();

            return response()->json([
                'message' => 'Informe actualizado correctamente',
                'data' => $informe
            ], 200);
        }
        return response()->json(['message' => 'Informe no encontrado'], 404);
    }

    /**
     * Obtener todos los informes.
     */
    public static function getAll()
    {
        $informes = Informe::all();
        return response()->json([
            'message' => 'Lista de informes',
            'data' => $informes
        ], 200);
    }

    /**
     * Obtener un informe por su código.
     */
    public static function getByCodigo($codigo)
    {
        $informe = Informe::find($codigo);
        if ($informe) {
            return response()->json([
                'message' => 'Informe encontrado',
                'data' => $informe
            ], 200);
        }
        return response()->json(['message' => 'Informe no encontrado'], 404);
    }

    /**
     * Eliminar un informe por su código.
     */
    public static function delete($codigo)
    {
        $informe = Informe::find($codigo);
        if ($informe) {
            $informe->delete();
            return response()->json(['message' => 'Informe eliminado correctamente'], 200);
        }
        return response()->json(['message' => 'Informe no encontrado'], 404);
    }
}
