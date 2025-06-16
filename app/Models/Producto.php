<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Producto extends Model
{
    protected $table = 'producto';
    protected $primaryKey = 'codigo';
    public $incrementing = false;
    public $timestamps = false;

    protected $fillable = [
        'codigo',
        'nombre',
        'descripcion',
        'precio',
        'stock',
        'urlImage',
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


    // Si necesitas relaciones, agrégalas aquí
}