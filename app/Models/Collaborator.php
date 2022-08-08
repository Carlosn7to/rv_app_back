<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Collaborator extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $table = 'colaboradores';
    protected $connection = 'mysql';
    protected $fillable = ['nome', 'funcao', 'canal', 'supervisor', 'tem_usuario', 'status_usuario'];

    public function meta()
    {
        return $this->hasOne(Meta::class, 'colaborador_id', 'id')->select('colaborador_id', 'meta');
    }
}
