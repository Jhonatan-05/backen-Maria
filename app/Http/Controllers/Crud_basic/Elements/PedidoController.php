<?php

namespace App\Http\Controllers\Crud_basic\Elements;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\pedidoLogic; // Asegúrate que sea App\Services\pedidoLogic
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Auth; // Para obtener el usuario autenticado

class PedidoController extends Controller
{
    protected $pedidoLogic;

    public function __construct(pedidoLogic $pedidoLogic)
    {
        $this->pedidoLogic = $pedidoLogic;
    }

    // Método para obtener todos los pedidos (para roles de personal)
    public function getAll()
    {
        return $this->pedidoLogic->getAll();
    }

    // Método para que el cliente autenticado vea sus propios pedidos
    public function getMisPedidos(Request $request)
    {
        $user = Auth::guard('cliente_api')->user();
        if (!$user) {
            return response()->json(['message' => 'Usuario no autenticado'], 401);
        }
        return $this->pedidoLogic->getByClienteId($user->cedula);
    }

    // Método para obtener un pedido por código (genérico)
    public function getByCodigo(Request $request)
    {
        return $this->pedidoLogic->getByCodigo($request->codigo);
    }

    /**
     * Crea un nuevo pedido (versión para personal/admin, requiere todos los campos).
     */
    public function create(Request $request)
    {
        try {
            $request->validate([
                'codigo' => 'required|string|max:20|unique:pedido,codigo',
                'idCliente' => 'required|string|exists:cliente,cedula',
                'idAsistenteVentas' => 'nullable|string|exists:asistenteventas,cedula', // Asistente puede ser null
                'direccion' => 'required|string|max:255',
                'fechaRegistro' => 'required|date',
                'estado' => 'required|string|max:50',
                'costoTotal' => 'required|numeric|min:0',
                'productos_con_cantidades' => 'nullable|array', // Array de objetos {codigo, cantidad}
                'productos_con_cantidades.*.codigo' => 'required|string|exists:producto,codigo',
                'productos_con_cantidades.*.cantidad' => 'required|integer|min:1',
            ]);
        } catch (ValidationException $e) {
            return response()->json(['message' => 'Error de validación', 'errors' => $e->errors()], 422);
        }

        $productos_con_cantidades = $request->input('productos_con_cantidades', []);

        return $this->pedidoLogic->update( // O el método de creación general si ya tienes uno
            $request->codigo,
            $request->idCliente,
            $request->idAsistenteVentas,
            $request->direccion,
            $request->fechaRegistro,
            $request->estado,
            $request->costoTotal,
            $productos_con_cantidades
        );
    }

    /**
     * NUEVO MÉTODO: Crea un nuevo pedido simplificado para el cliente autenticado.
     * Solo requiere dirección, fecha y productos con cantidades del request.
     */
    public function createClientPedido(Request $request)
    {
        // Obtener el cliente autenticado
        $user = Auth::guard('cliente_api')->user();
        if (!$user) {
            return response()->json(['message' => 'Usuario no autenticado'], 401);
        }

        try {
            $request->validate([
                'direccion' => 'required|string|max:255',
                'fechaRegistro' => 'required|date|after_or_equal:today', // Fecha debe ser hoy o en el futuro
                'productos_con_cantidades' => 'required|array|min:1', // Al menos un producto es requerido
                'productos_con_cantidades.*.codigo' => 'required|string|exists:producto,codigo',
                'productos_con_cantidades.*.cantidad' => 'required|integer|min:1',
            ]);
        } catch (ValidationException $e) {
            return response()->json(['message' => 'Error de validación', 'errors' => $e->errors()], 422);
        }

        $direccion = $request->input('direccion');
        $fechaRegistro = $request->input('fechaRegistro');
        $productos_con_cantidades = $request->input('productos_con_cantidades');
        $idCliente = $user->cedula; // Cliente autenticado
        $idAsistenteVentas = null; // Siempre null para pedidos iniciados por cliente

        return $this->pedidoLogic->createClientPedido( // Llamar al nuevo método de pedidoLogic
            $idCliente,
            $direccion,
            $fechaRegistro,
            $productos_con_cantidades,
            $idAsistenteVentas
        );
    }

    /**
     * Actualiza un pedido existente (versión genérica).
     */
    public function update(Request $request)
    {
        try {
            $request->validate([
                'codigo' => 'required|string|exists:pedido,codigo',
                'idCliente' => 'required|string|exists:cliente,cedula',
                'idAsistenteVentas' => 'nullable|string|exists:asistenteventas,cedula',
                'direccion' => 'required|string|max:255',
                'fechaRegistro' => 'required|date',
                'estado' => 'required|string|max:50',
                'costoTotal' => 'required|numeric|min:0',
                'productos_con_cantidades' => 'nullable|array',
                'productos_con_cantidades.*.codigo' => 'required|string|exists:producto,codigo',
                'productos_con_cantidades.*.cantidad' => 'required|integer|min:1',
            ]);
        } catch (ValidationException $e) {
            return response()->json(['message' => 'Error de validación', 'errors' => $e->errors()], 422);
        }

        $productos_con_cantidades = $request->input('productos_con_cantidades', []);

        return $this->pedidoLogic->update(
            $request->codigo,
            $request->idCliente,
            $request->idAsistenteVentas,
            $request->direccion,
            $request->fechaRegistro,
            $request->estado,
            $request->costoTotal,
            $productos_con_cantidades
        );
    }

    public function destroy(Request $request)
    {
        return $this->pedidoLogic->delete($request->codigo);
    }
}
