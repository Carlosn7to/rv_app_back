<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Meta extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = ['colaborador_id', 'meta', 'mes', 'criado_por'];
    protected $table = 'meta_colaborador';
    protected $connection = 'mysql';
}
