<?php

namespace App\Http\Controllers;

use App\Models\DataVoalle;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TestController extends Controller
{
    public function index(Request $request)
    {

        $supervisor = 'Keila Jaqueline da Silva';

        $query = 'SELECT SUM(valor) as "Valor", MONTHNAME(data_contrato) AS "Mês", YEAR(data_contrato) as "Ano"
                    FROM data_voalle WHERE supervisor = "'.$supervisor.'"
                    GROUP BY YEAR(data_contrato), MONTH(data_contrato)';

        $query = DB::select($query);

        foreach ($query as $field => $valor) {
            $valor->Valor = $valor->Valor/100;
        }


        return response()->json($query);

    }
}
