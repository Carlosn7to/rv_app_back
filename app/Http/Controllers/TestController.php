<?php

namespace App\Http\Controllers;

use App\Models\Collaborator;
use App\Models\Contract;
use App\Models\DataVoalle;
use App\Models\Meta;
use Carbon\Carbon;
use Carbon\Traits\Date;
use Dflydev\DotAccessData\Data;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use function PHPUnit\Framework\isNull;

class TestController extends Controller
{
    public function index(Request $request)
    {

        $array1 = [
            'nome' => 'carlos neto',
            'idade' => 23,
            'funcao' => 'DEV'
        ];

        $array2 = [
            'nome' => 'Vinicius',
            'idade' => 40,
            'funcao' => 'DEV Mobile'
        ];

        $arrayConcat = [];

        $arrayConcat[] = $array1;


        return $arrayConcat;

    }
}
