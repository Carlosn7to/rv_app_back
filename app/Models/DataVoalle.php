<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DataVoalle extends Model
{
    use HasFactory;

    protected $table = 'data_voalle';
    protected $fillable = ['id_contrato', 'nome_cliente', 'status', 'data_contrato', 'data_ativacao', 'conexao',
                            'vendedor', 'supervisor', 'data_cancelamento', 'plano'];


}
