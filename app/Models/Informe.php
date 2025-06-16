<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Informe extends Model
{
    protected $table = 'informe';
    protected $primaryKey = 'codigo';
    public $incrementing = false;
    public $timestamps = false;

    protected $fillable = [
        'codigo',
        'idEspecialista',
        'nombre',
        'descripción',
        'codigoCita',
    ];

    /**
     * Relación con Especialista
     * Un Informe pertenece a un Especialista.
     */
    public function especialista()
    {
        return $this->belongsTo(Especialista::class, 'idEspecialista', 'cedula');
    }

    /**
     * Relación con Cita
     * Un Informe pertenece a una Cita.
     */
    public function cita()
    {
        return $this->belongsTo(Cita::class, 'codigoCita', 'codigo');
    }
}