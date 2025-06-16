<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Pedido extends Model
{
    protected $table = 'pedido';
    protected $primaryKey = 'codigo';
    public $incrementing = false;
    public $timestamps = false;

    protected $fillable = [
        'codigo',
        'idCliente',
        'idAsistenteVentas',
        'direccion',
        'fechaRegistro',
        'estado',
        'costoTotal',
    ];

    /**
     * Relación con Cliente
     * Un pedido pertenece a un cliente.
     */
    public function cliente()
    {
        return $this->belongsTo(Cliente::class, 'idCliente', 'cedula');
    }

    /**
     * Relación con Asistente de Ventas
     * Un pedido pertenece a un asistente de ventas.
     */
    public function asistenteVentas()
    {
        return $this->belongsTo(AsistenteVentas::class, 'idAsistenteVentas', 'cedula');
    }

    /**
     * Relación con productos (muchos a muchos)
     */
    public function productos()
    {
        return $this->belongsToMany(
            Producto::class,
            'contienePedido',
            'codigoPedido',
            'codigoProducto'
        )->withPivot('numProductos');
    }
}