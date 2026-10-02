<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Emprendedor extends Model
{
    use HasFactory;

protected $table = 'emprendedor';
protected $primaryKey = 'idEmprendedor';

    protected $fillable = [
        'nombreEmprendedor',
        'apellido',
        'domicilio',
        'contacto',
        'email',
        'dni',
        'formalizacion',
    ];

    public function emprendimientos()
    {
        return $this->belongsToMany(
            Emprendimiento::class,
            'emprendedor_emprendimiento',
            'idEmprendedor',
            'idEmprendimiento'
        );
    }
}
