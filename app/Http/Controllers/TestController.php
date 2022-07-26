<?php

namespace App\Http\Controllers;

use App\Models\DataVoalle;
use App\Models\Usuario;
use Illuminate\Http\Request;

class TestController extends Controller
{
    public function index(Request $request)
    {

        $vendas = DataVoalle::select('id', 'id_contrato', 'nome_cliente', 'status', 'situacao', 'data_contrato', 'data_ativacao', 'conexao',
                                        'vendedor', 'supervisor', 'data_cancelamento', 'plano')->where('vendedor', $request->header('vendedor'))
                                        ->whereDate('data_ativacao', '>=', date($request->header('data_inicial')))
                                        ->whereDate('data_ativacao', '<=', date($request->header('data_final')))
                                        ->get();


        return response()->json($vendas);

    }
}
