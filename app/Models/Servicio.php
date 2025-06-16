<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Servicio extends Model
{
    use HasFactory;

    protected $table = 'servicio';
    public $incrementing = false;
    protected $keyType = 'string';
    protected $primaryKey = 'codigo';

    protected $fillable = [
        'codigo', 'nombre', 'descripcion', 'precio', 'urlImage',
    ];

    public function citas(): BelongsToMany
    {
        return $this->belongsToMany(Cita::class, 'contieneCita', 'codigoServicio', 'codigoCita');
        // ->withTimestamps(); // Descomenta si añade timestamps a 'contieneCita'
    }

    // Este es el cambio clave:
    protected $appends = ['full_image_url']; // Esto le dice a Eloquent que incluya 'full_image_url' en la salida JSON

    /**
     * Obtiene la URL completa de la imagen del cliente.
     * Esto se añade automáticamente cuando el modelo se convierte a array/JSON.
     *
     * @return string|null
     */
    public function getFullImageUrlAttribute()
    {
        // Verifica si existe una urlImage y genera la URL completa usando el helper asset()
        if ($this->urlImage) {
            return asset($this->urlImage);
        }
        return null; // O puedes devolver una URL a una imagen de placeholder si no hay imagen
    }
}