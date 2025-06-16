<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Cita extends Model
{
    use HasFactory;

    protected $table = 'cita';
    public $incrementing = false;
    protected $keyType = 'string';
    protected $primaryKey = 'codigo';

    protected $fillable = [
        'codigo', 'idCliente', 'idRecepcionista', 'fechaCita', 'estado', 'costoTotal',
    ];

    // Convertir fechaCita a objeto Carbon
    protected $casts = [
        'fechaCita' => 'datetime',
    ];

    public $timestamps = false;

    /**
     * Relación con el modelo Cliente.
     * Un Cita pertenece a un Cliente.
     */
    public function cliente(): BelongsTo
    {
        return $this->belongsTo(Cliente::class, 'idCliente', 'cedula');
    }

    /**
     * Relación con el modelo Recepcionista.
     * Un Cita pertenece a un Recepcionista.
     */
    public function recepcionista(): BelongsTo
    {
        return $this->belongsTo(Recepcionista::class, 'idRecepcionista', 'cedula');
    }

    /**
     * Relación con el modelo Servicio.
     * Un Cita puede tener muchos Servicios a través de la tabla pivote 'contieneCita'.
     */
    public function servicios(): BelongsToMany
    {
        return $this->belongsToMany(Servicio::class, 'contieneCita', 'codigoCita', 'codigoServicio');
        // ->withTimestamps(); // Descomenta si añade timestamps a 'contieneCita'
    }
}