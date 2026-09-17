<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Evento extends Model
{
    use HasFactory;

    protected $table = 'evento';
    protected $primaryKey = 'idEvento';
    public $timestamps = false;

    protected $fillable = [
        'nombreEvento',
        'ciudad',
        'lugar',
        'fecha',
        'tipoDeEvento',
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