<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Emprendimiento extends Model
{
    use HasFactory;

<<<<<<< HEAD:app/Models/emprendimiento.php
    protected $table = 'emprendimiento';
    protected $primaryKey = 'idEmprendimiento';
=======
protected $table = 'emprendimiento';
protected $primaryKey = 'idEmprendimiento';
>>>>>>> origin/main:app/Models/Emprendimiento.php

    protected $fillable = [
        'idEmprendedor',
        'nombreEmprendimiento',
        'rubro',
        'estado',
        'descripcion',
    ];

    public function emprendedor()
    {
        return $this->belongsTo(Emprendedor::class, 'idEmprendedor', 'idEmprendedor');
    }

    public function necesidades()
    {
        return $this->hasMany(Necesidad::class, 'idEmprendimiento', 'idEmprendimiento');
    }

    public function actividades()
    {
        return $this->hasMany(Actividad::class, 'idEmprendimiento', 'idEmprendimiento');
    }

    // Relación con eventos a través de la tabla "asistencia"
    public function eventosAsignados()
    {
        return $this->belongsToMany(
            Evento::class,
            'asistencia',
            'idEmprendimiento',
            'idEvento',
            'idEmprendimiento',
            'idEvento'
        );
    }
}