<?php

namespace App\Services;

use App\Models\Cita;
use App\Models\Cliente;
use App\Models\Recepcionista;
use App\Models\Servicio;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str; // Para generar códigos UUID/random

class CitaLogic
{
    /**
     * Obtener todas las citas, incluyendo la información del cliente, recepcionista y servicios relacionados.
     */
    public static function getAll()
    {
        $citas = Cita::with(['cliente', 'recepcionista', 'servicios'])->get();
        return response()->json([
            'message' => 'Lista de citas',
            'data' => $citas->toArray()
        ], 200);
    }

    /**
     * Obtener citas por ID de Cliente, incluyendo la información del cliente, recepcionista y servicios relacionados.
     */
    public static function getByClienteId(string $idCliente)
    {
        $citas = Cita::where('idCliente', $idCliente)
                      ->with(['cliente', 'recepcionista', 'servicios'])
                      ->get();

        if ($citas->isEmpty()) {
            return response()->json([
                'message' => 'No se encontraron citas para este cliente',
                'data' => []
            ], 200);
        }

        return response()->json([
            'message' => 'Lista de citas del cliente',
            'data' => $citas->toArray()
        ], 200);
    }

    /**
     * Obtener una cita por su código, incluyendo la información del cliente, recepcionista y servicios relacionados.
     */
    public static function getByCodigo($codigo)
    {
        $cita = Cita::with(['cliente', 'recepcionista', 'servicios'])->find($codigo);
        if (!$cita) {
            return response()->json(['message' => 'Cita no encontrada'], 404);
        }
        return response()->json([
            'message' => 'Cita encontrada',
            'data' => $cita->toArray()
        ], 200);
    }

        /**
     * Crear una nueva cita con generación automática de código y asociación de recepcionista.
     * El recepcionista puede ser null si no aplica (ej. cita interna o para otro tipo de personal).
     *
     * @param string $idCliente La cédula del cliente.
     * @param string|null $idRecepcionista La cédula del recepcionista (opcional).
     * @param string $fechaCita La fecha y hora de la cita.
     * @param string $estado El estado de la cita (ej. 'Pendiente').
     * @param float $costoTotal El costo total de la cita.
     * @param array $servicio_codigos Array de códigos de servicios asociados.
     * @return \Illuminate\Http\JsonResponse
     */
    public static function create(string $idCliente, ?string $idRecepcionista, string $fechaCita, string $estado, float $costoTotal, array $servicio_codigos = [])
    {
        try {
            DB::beginTransaction();

            // Generar un código único para la cita
            $codigo = 'CITA-' . Str::uuid()->toString();

            $cita = Cita::create([
                'codigo' => $codigo, // Código generado automáticamente
                'idCliente' => $idCliente,
                'idRecepcionista' => $idRecepcionista, // Puede ser null
                'fechaCita' => $fechaCita,
                'estado' => $estado,
                'costoTotal' => $costoTotal,
            ]);

            // Sincronizar servicios (adjuntar servicios a la cita a través de la tabla pivote)
            if (!empty($servicio_codigos)) {
                // Verificar que los códigos de servicio existen antes de adjuntar
                $existingServiceCodes = Servicio::whereIn('codigo', $servicio_codigos)->pluck('codigo')->toArray();
                $cita->servicios()->sync($existingServiceCodes);
            } else {
                $cita->servicios()->detach(); // Si no hay servicios, desvincular todos
            }

            DB::commit();

            // Recargar el modelo con todas las relaciones para la respuesta JSON
            $cita->load(['cliente', 'recepcionista', 'servicios']);

            return response()->json([
                'message' => 'Cita creada exitosamente',
                'data' => $cita->toArray()
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error("Error al crear cita y/o asociar servicios: " . $e->getMessage());
            return response()->json(['message' => 'Error al crear la cita y/o asociar servicios.', 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * Crear una nueva cita con generación automática de código y cliente, y recepcionista opcional.
     *
     * @param string $idCliente La cédula del cliente (viene del usuario autenticado).
     * @param string $fechaCita La fecha y hora de la cita.
     * @param array $servicio_codigos Array de códigos de servicio a asociar.
     * @param string|null $idRecepcionista (Opcional) La cédula del recepcionista, si aplica.
     * @return \Illuminate\Http\JsonResponse
     */
    public static function createClientCita(string $idCliente, string $fechaCita, array $servicio_codigos = [], ?string $idRecepcionista = null) // <-- CAMBIO DE PARÁMETROS
    {
        try {
            DB::beginTransaction();

            // Generar un código único para la cita
            $codigo = 'CITA-' . Str::uuid()->toString(); // Usar UUID para generar un código único
            // O un generador de secuencia si tienes uno, ej: 'CITA-' . uniqid();
            // Asegúrate de que el formato de 'codigo' en tu DB sea varchar y suficientemente largo para un UUID

            // Asignar el estado "Pendiente" por defecto
            $estado = 'Pendiente';

            // Calcular el costo total de los servicios seleccionados
            $costoTotal = Servicio::whereIn('codigo', $servicio_codigos)->sum('precio');

            $cita = Cita::create([
                'codigo' => $codigo, // Código generado
                'idCliente' => $idCliente, // Viene del usuario autenticado
                'idRecepcionista' => $idRecepcionista, // Puede ser null
                'fechaCita' => $fechaCita,
                'estado' => $estado, // Estado por defecto
                'costoTotal' => $costoTotal // Costo calculado
            ]);

            // Asociar los servicios a la cita
            if (!empty($servicio_codigos)) {
                $cita->servicios()->sync($servicio_codigos);
            }

            DB::commit();

            // Recargar el modelo con todas las relaciones para la respuesta JSON
            $cita->load(['cliente', 'recepcionista', 'servicios']);

            return response()->json([
                'message' => 'Cita creada exitosamente',
                'data' => $cita->toArray()
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error("Error al crear cita y asociar servicios: " . $e->getMessage());
            return response()->json(['message' => 'Error al crear la cita y/o asociar servicios.', 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * Actualizar una cita existente.
     * Este método se mantiene genérico para recepcionistas/admin.
     * Para cliente, se puede extender si se permiten cambios de otros campos aparte de servicios.
     */
    public static function update($codigo, $idCliente, $idRecepcionista, $fechaCita, $estado, $costoTotal, array $servicio_codigos = [])
    {
        try {
            DB::beginTransaction();

            $cita = Cita::find($codigo);
            if (!$cita) {
                DB::rollBack();
                return response()->json(['message' => 'Cita no encontrada'], 404);
            }

            $cita->idCliente = $idCliente;
            $cita->idRecepcionista = $idRecepcionista;
            $cita->fechaCita = $fechaCita;
            $cita->estado = $estado;
            $cita->costoTotal = $costoTotal; // Este campo podría recalcularse en la actualización si los servicios cambian
            $cita->save();

            // Sincronizar los servicios al actualizar la cita
            if (!empty($servicio_codigos)) {
                $cita->servicios()->sync($servicio_codigos);
            } else {
                $cita->servicios()->detach();
            }

            DB::commit();

            // Recargar el modelo con todas las relaciones para la respuesta JSON
            $cita->load(['cliente', 'recepcionista', 'servicios']);

            return response()->json([
                'message' => 'Cita actualizada correctamente',
                'data' => $cita->toArray()
            ], 200);

        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error("Error al actualizar cita y asociar servicios: " . $e->getMessage());
            return response()->json(['message' => 'Error al actualizar la cita y/o asociar servicios.', 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * Eliminar una cita por su código.
     */
    public static function delete($codigo)
    {
        $cita = Cita::find($codigo);
        if (!$cita) {
            return response()->json(['message' => 'Cita no encontrada'], 404);
        }

        $cita->delete();
        return response()->json(['message' => 'Cita eliminada correctamente'], 200);
    }
}
