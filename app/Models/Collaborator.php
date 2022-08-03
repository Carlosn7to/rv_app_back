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
    protected $fillable = ['nome', 'funcao', 'canal'];
}
