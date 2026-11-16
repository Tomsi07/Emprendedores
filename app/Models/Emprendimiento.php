<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Emprendimiento extends Model
{
    use HasFactory;

protected $table = 'emprendimientos';
protected $primaryKey = 'idEmprendimiento';

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
}
