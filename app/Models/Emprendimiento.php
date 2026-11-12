<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Emprendimiento extends Model
{
    use HasFactory;

    protected $table = 'emprendimiento';
    protected $primaryKey = 'idEmprendimiento';
    public $timestamps = false;

    protected $fillable = [
        'nombreEmprendimiento',
        'contacto',
        'domicilio',
        'estado',
        'productosServicios',
    ];

    public function emprendedores()
    {
        return $this->belongsToMany(
            Emprendedor::class,
            'emprendedor_emprendimiento',
            'idEmprendimiento',
            'idEmprendedor',
            'idEmprendimiento',
            'idEmprendedor'
        );
    }

    public function necesidades()
    {
        return $this->hasMany(Necesidad::class, 'idEmprendimiento', 'idEmprendimiento');
    }

    public function actividades()
    {
        return $this->belongsToMany(
            Actividad::class,
            'emprendimiento_actividad',
            'idEmprendimiento',
            'idActividad',
            'idEmprendimiento',
            'idActividad'
        );
    }
}