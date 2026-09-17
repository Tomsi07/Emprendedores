<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Actividad extends Model
{
    use HasFactory;

    protected $table = 'actividades';
    protected $primaryKey = 'idActividad';

    protected $fillable = [
        'idEmprendimiento',
        'nombreActividad',
        'descripcion',
        'fechaInicio',
        'fechaFin',
        'estado',
    ];

    public function emprendimiento()
    {
        return $this->belongsTo(Emprendimiento::class, 'idEmprendimiento', 'idEmprendimiento');
    }
}