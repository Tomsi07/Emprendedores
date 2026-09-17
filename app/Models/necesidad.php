<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Necesidad extends Model
{
    use HasFactory;

    protected $table = 'necesidades';
    protected $primaryKey = 'idNecesidad';

    protected $fillable = [
        'idEmprendimiento',
        'titulo',
        'tipo',
        'descripcion',
        'prioridad',
        'estado',
    ];

    public function emprendimiento()
    {
        return $this->belongsTo(Emprendimiento::class, 'idEmprendimiento', 'idEmprendimiento');
    }
}