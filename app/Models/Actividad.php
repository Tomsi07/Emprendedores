<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Actividad extends Model
{
    use HasFactory;

    protected $table = 'actividad';
    protected $primaryKey = 'idActividad';
    public $timestamps = false;

    protected $fillable = [
        'idEmprendimiento',
        'nombreActividad',
        'descripcion',
        'fechaInicio',
        'fechaFin',
        'estado',
        'especializacion',
    ];

    public function emprendimiento()
    {
        return $this->belongsTo(Emprendimiento::class, 'idEmprendimiento', 'idEmprendimiento');
    }
}
