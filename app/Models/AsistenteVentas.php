<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;

class AsistenteVentas extends Authenticatable
{
    use HasFactory, Notifiable, HasApiTokens, HasRoles;

    protected $table = 'asistenteVentas';
    public $incrementing = false;
    protected $keyType = 'string';
    protected $primaryKey = 'cedula';

    protected $fillable = [
        'cedula', 'nombre', 'email', 'password', 'edad', 'sexo', 'salario', 'urlImage'
    ];

    protected $hidden = [
        'password',
    ];

    protected $casts = [
        'password' => 'hashed',
    ];

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