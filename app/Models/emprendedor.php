<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Emprendedor extends Model
{
    use HasFactory;

protected $table = 'emprendedores';
protected $primaryKey = 'idEmprendedor';

    protected $fillable = [
        'nombreEmprendedor',
        'apellido',
        'telefono',
        'email',
    ];

    public function emprendimientos()
    {
        return $this->hasMany(Emprendimiento::class, 'idEmprendedor', 'idEmprendedor');
    }
}