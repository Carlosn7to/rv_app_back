<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DataVoalle extends Model
{
    use HasFactory;

    protected $table = 'data_voalle';
    protected $fillable = ['id_contrato', 'nome_cliente', 'status', 'situacao', 'valor', 'data_contrato', 'data_ativacao', 'conexao',
                            'vendedor', 'supervisor', 'data_cancelamento', 'plano'];
    protected $connection = 'mysql';


    public function channels()
    {
        return $this->hasOne(Collaborator::class, 'nome', 'vendedor')->select('nome','canal');
    }

    public function plans_supervisor()
    {
        return $this->hasMany(DataVoalle::class, 'supervisor', 'supervisor')
            ->select('supervisor', 'plano');
    }

    public function plans_vendors()
    {
        return $this->hasMany(DataVoalle::class, 'vendedor', 'vendedor')
            ->select('vendedor', 'plano');
    }

}
