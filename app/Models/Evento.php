<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Evento extends Model
{
    use HasFactory;

    protected $table = 'eventos';
    protected $primaryKey = 'idEvento';
    public $timestamps = false;

  protected $fillable = [
    'nombreEvento',
    'ciudad',
    'lugar',
    'tipoDeEvento',
    'fecha',
    'fechaFin',
];
    public function emprendimientos()
    {
        return $this->belongsToMany(
            Emprendimiento::class,
            'asistencia',
            'idEvento',
            'idEmprendimiento'
        );
    }
}
