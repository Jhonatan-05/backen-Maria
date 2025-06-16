<?php

namespace App\Services;

use App\Models\Pedido;
use App\Models\Producto; // Necesario para calcular el costo
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str; // Para generar códigos UUID/random

class pedidoLogic
{
    /**
     * Obtener todos los pedidos, incluyendo la información del cliente, asistente de ventas y productos relacionados.
     */
    public static function getAll()
    {
        $pedidos = Pedido::with(['cliente', 'asistenteVentas', 'productos'])->get();
        return response()->json([
            'message' => 'Lista de pedidos',
            'data' => $pedidos->toArray()
        ], 200);
    }

    /**
     * Obtener pedidos por ID de Cliente, incluyendo la información del cliente, asistente de ventas y productos relacionados.
     */
    public static function getByClienteId(string $idCliente)
    {
        $pedidos = Pedido::where('idCliente', $idCliente)
                      ->with(['cliente', 'asistenteVentas', 'productos'])
                      ->get();

        if ($pedidos->isEmpty()) {
            return response()->json([
                'message' => 'No se encontraron pedidos para este cliente',
                'data' => []
            ], 200);
        }

        return response()->json([
            'message' => 'Lista de pedidos del cliente',
            'data' => $pedidos->toArray()
        ], 200);
    }

    /**
     * Obtener un pedido por su código, incluyendo la información del cliente, asistente de ventas y productos relacionados.
     */
    public static function getByCodigo($codigo)
    {
        $pedido = Pedido::with(['cliente', 'asistenteVentas', 'productos'])->find($codigo);
        if (!$pedido) {
            return response()->json(['message' => 'Pedido no encontrado'], 404);
        }
        return response()->json([
            'message' => 'Pedido encontrado',
            'data' => $pedido->toArray()
        ], 200);
    }

    /**
     * NUEVO MÉTODO: Crear un nuevo pedido con generación automática de código y cliente, y asistente de ventas opcional.
     *
     * @param string $idCliente La cédula del cliente (viene del usuario autenticado).
     * @param string $direccion La dirección de envío del pedido.
     * @param string $fechaRegistro La fecha y hora de registro del pedido.
     * @param array $productos_con_cantidades Array de objetos {codigo: string, cantidad: number}.
     * @param string|null $idAsistenteVentas (Opcional) La cédula del asistente de ventas, si aplica.
     * @return \Illuminate\Http\JsonResponse
     */
    public static function createClientPedido(string $idCliente, string $direccion, string $fechaRegistro, array $productos_con_cantidades = [], ?string $idAsistenteVentas = null)
    {
        try {
            DB::beginTransaction();

            // Generar un código único para el pedido
            $codigo = 'PEDIDO-' . Str::uuid()->toString();

            // Asignar el estado "Pendiente" por defecto
            $estado = 'Pendiente';

            // Calcular el costo total de los productos seleccionados
            $totalCosto = 0;
            $syncData = []; // Para la tabla pivote con cantidades

            foreach ($productos_con_cantidades as $item) {
                $producto = Producto::where('codigo', $item['codigo'])->first();
                if ($producto) {
                    $totalCosto += ($producto->precio * $item['cantidad']);
                    $syncData[$item['codigo']] = ['numProductos' => $item['cantidad']];
                }
            }

            $pedido = Pedido::create([
                'codigo' => $codigo, // Código generado
                'idCliente' => $idCliente, // Viene del usuario autenticado
                'idAsistenteVentas' => $idAsistenteVentas, // Puede ser null
                'direccion' => $direccion,
                'fechaRegistro' => $fechaRegistro,
                'estado' => $estado, // Estado por defecto
                'costoTotal' => $totalCosto // Costo calculado
            ]);

            // Asociar los productos al pedido con sus cantidades
            if (!empty($syncData)) {
                $pedido->productos()->sync($syncData);
            }

            DB::commit();

            // Recargar el modelo con todas las relaciones para la respuesta JSON
            $pedido->load(['cliente', 'asistenteVentas', 'productos']);

            return response()->json([
                'message' => 'Pedido creado exitosamente',
                'data' => $pedido->toArray()
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error("Error al crear pedido y/o asociar productos: " . $e->getMessage());
            return response()->json(['message' => 'Error al crear el pedido y/o asociar productos.', 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * Actualizar un pedido existente.
     * Este método se mantiene genérico para asistentes de ventas/admin.
     */
    public static function update($codigo, $idCliente, $idAsistenteVentas, $direccion, $fechaRegistro, $estado, $costoTotal, array $productos_con_cantidades = [])
    {
        try {
            DB::beginTransaction();

            $pedido = Pedido::find($codigo);
            if (!$pedido) {
                DB::rollBack();
                return response()->json(['message' => 'Pedido no encontrado'], 404);
            }

            $pedido->idCliente = $idCliente;
            $pedido->idAsistenteVentas = $idAsistenteVentas;
            $pedido->direccion = $direccion;
            $pedido->fechaRegistro = $fechaRegistro;
            $pedido->estado = $estado;
            $pedido->costoTotal = $costoTotal; // Este campo podría recalcularse en la actualización
            $pedido->save();

            // Sincronizar los productos al actualizar el pedido
            $syncData = [];
            foreach ($productos_con_cantidades as $item) {
                $syncData[$item['codigo']] = ['numProductos' => $item['cantidad']];
            }
            if (!empty($syncData)) {
                $pedido->productos()->sync($syncData);
            } else {
                $pedido->productos()->detach(); // Quitar todos los productos si no se pasan
            }

            DB::commit();

            // Recargar el modelo con todas las relaciones para la respuesta JSON
            $pedido->load(['cliente', 'asistenteVentas', 'productos']);

            return response()->json([
                'message' => 'Pedido actualizado correctamente',
                'data' => $pedido->toArray()
            ], 200);

        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error("Error al actualizar pedido y/o asociar productos: " . $e->getMessage());
            return response()->json(['message' => 'Error al actualizar el pedido y/o asociar productos.', 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * Eliminar un pedido por su código.
     */
    public static function delete($codigo)
    {
        $pedido = Pedido::find($codigo);
        if (!$pedido) {
            return response()->json(['message' => 'Pedido no encontrado'], 404);
        }

        $pedido->delete();
        return response()->json(['message' => 'Pedido eliminado correctamente'], 200);
    }
}
