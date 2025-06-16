<?php

namespace App\Http\Controllers\Crud_basic\Elements;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\CitaLogic;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Auth;

class CitaController extends Controller
{
    protected $citaLogic;

    public function __construct(CitaLogic $citaLogic)
    {
        $this->citaLogic = $citaLogic;
    }

    // Método para obtener todas las citas (para roles de personal)
    public function getAll()
    {
        return $this->citaLogic->getAll();
    }

    // Método para que el cliente autenticado vea sus propias citas
    public function getMisCitas(Request $request)
    {
        $user = Auth::guard('cliente_api')->user();
        if (!$user) {
            return response()->json(['message' => 'Usuario no autenticado'], 401);
        }
        return $this->citaLogic->getByClienteId($user->cedula);
    }

    // Método para obtener una cita por código (genérico)
    public function getByCodigo(Request $request)
    {
        return $this->citaLogic->getByCodigo($request->codigo);
    }

    /**
     * Crea una nueva cita (versión para personal/admin, requiere todos los campos).
     */
    public function create(Request $request)
    {
        try {
            $request->validate([
                'codigo' => 'required|string|unique:cita,codigo',
                'idCliente' => 'required|string|exists:cliente,cedula',
                'idRecepcionista' => 'nullable|string|exists:recepcionista,cedula', // Recepcionista puede ser null
                'fechaCita' => 'required|date',
                'estado' => 'required|string|max:50',
                'costoTotal' => 'required|numeric|min:0',
                'servicio_codigos' => 'nullable|array',
                'servicio_codigos.*' => 'string|exists:servicio,codigo',
            ]);
        } catch (ValidationException $e) {
            return response()->json(['message' => 'Error de validación', 'errors' => $e->errors()], 422);
        }

        $idCliente = $request->input('idCliente');
        $fechaCita = $request->input('fechaCita');
        $estado = $request->input('estado');
        $costoTotal = $request->input('costoTotal');

        $servicio_codigos = $request->input('servicio_codigos', []);

        // Determinar el idRecepcionista:
        // Si el usuario autenticado es un recepcionista, usar su cédula.
        // Si no, usar el valor proporcionado en la solicitud (que puede ser null).
        $authRecepcionista = Auth::guard('recepcionista_api')->user();
        $idRecepcionista = $authRecepcionista ? $authRecepcionista->cedula : $request->input('idRecepcionista');    

        // Re-calcule el costo total en el backend si es necesario para mayor seguridad
        // $costoTotal = Servicio::whereIn('codigo', $servicio_codigos)->sum('precio');

        return $this->citaLogic->create( // Usar el método 'create' general
            $idCliente,
            $idRecepcionista,
            $fechaCita,
            $estado,
            $costoTotal, // Puedes pasar el costo calculado por frontend o recalcular aquí
            $servicio_codigos
        );
    }

    /**
     * NUEVO MÉTODO: Crea una nueva cita simplificada para el cliente autenticado.
     * Solo requiere fecha y servicios del request.
     */
    public function createClientCita(Request $request)
    {
        // Obtener el cliente autenticado
        $user = Auth::guard('cliente_api')->user();
        if (!$user) {
            return response()->json(['message' => 'Usuario no autenticado'], 401);
        }

        try {
            $request->validate([
                'fechaCita' => 'required|date|after_or_equal:today', // Fecha debe ser hoy o en el futuro
                'servicio_codigos' => 'required|array|min:1', // Al menos un servicio es requerido
                'servicio_codigos.*' => 'string|exists:servicio,codigo',
            ]);
        } catch (ValidationException $e) {
            return response()->json(['message' => 'Error de validación', 'errors' => $e->errors()], 422);
        }

        $fechaCita = $request->input('fechaCita');
        $servicio_codigos = $request->input('servicio_codigos');
        $idCliente = $user->cedula; // Cliente autenticado
        $idRecepcionista = null; // Siempre null para citas iniciadas por cliente

        return $this->citaLogic->createClientCita( // Llamar al nuevo método de CitaLogic
            $idCliente,
            $fechaCita,
            $servicio_codigos,
            $idRecepcionista
        );
    }

    /**
     * Actualiza una cita existente (versión genérica).
     */
    public function update(Request $request)
    {
        try {
            $request->validate([
                'codigo' => 'required|string|exists:cita,codigo',
                'idCliente' => 'required|string|exists:cliente,cedula',
                'idRecepcionista' => 'nullable|string|exists:recepcionista,cedula',
                'fechaCita' => 'required|date',
                'estado' => 'required|string|max:50',
                'costoTotal' => 'required|numeric|min:0',
                'servicio_codigos' => 'nullable|array',
                'servicio_codigos.*' => 'string|exists:servicio,codigo',
            ]);
        } catch (ValidationException $e) {
            return response()->json(['message' => 'Error de validación', 'errors' => $e->errors()], 422);
        }

        $servicio_codigos = $request->input('servicio_codigos', []);

        return $this->citaLogic->update(
            $request->codigo,
            $request->idCliente,
            $request->idRecepcionista,
            $request->fechaCita,
            $request->estado,
            $request->costoTotal,
            $servicio_codigos
        );
    }

    public function destroy(Request $request)
    {
        return $this->citaLogic->delete($request->codigo);
    }
}
