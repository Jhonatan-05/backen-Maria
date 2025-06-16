<?php

namespace App\Http\Controllers\Crud_basic\Elements;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\InformeLogic;
use Illuminate\Validation\ValidationException;

class InformeController extends Controller
{
    protected $informeLogic;

    public function __construct(InformeLogic $informeLogic)
    {
        $this->informeLogic = $informeLogic;
    }

    public function getAll()
    {
        return $this->informeLogic->getAll();
    }

    public function getByCodigo(Request $request)
    {
        return $this->informeLogic->getByCodigo($request->codigo);
    }

    public function create(Request $request)
    {
        try {
            $request->validate([
                'codigo' => 'required|string|max:20|unique:informe,codigo',
                'idEspecialista' => 'required|string|exists:especialista,cedula',
                'nombre' => 'required|string|max:255',
                'descripcion' => 'required|string|max:500',
                'codigoCita' => 'required|string|exists:cita,codigo',
            ]);
        } catch (ValidationException $e) {
            return response()->json(['message' => 'Error de validación', 'errors' => $e->errors()], 422);
        }
        return $this->informeLogic->create(
            $request->codigo,
            $request->idEspecialista,
            $request->nombre,
            $request->descripcion,
            $request->codigoCita
        );
    }

    public function update(Request $request)
    {
        try {
            $request->validate([
                'codigo' => 'required|string|exists:informe,codigo',
                'idEspecialista' => 'required|string|exists:especialista,cedula',
                'nombre' => 'required|string|max:255',
                'descripcion' => 'required|string|max:500',
                'codigoCita' => 'required|string|exists:cita,codigo',
            ]);
        } catch (ValidationException $e) {
            return response()->json(['message' => 'Error de validación', 'errors' => $e->errors()], 422);
        }
        return $this->informeLogic->update(
            $request->codigo,
            $request->idEspecialista,
            $request->nombre,
            $request->descripcion,
            $request->codigoCita
        );
    }

    public function destroy(Request $request)
    {
        return $this->informeLogic->delete($request->codigo);
    }
}
