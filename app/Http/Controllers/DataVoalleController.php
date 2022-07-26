<?php

namespace App\Http\Controllers;

use App\Models\DataVoalle;
use Dflydev\DotAccessData\Data;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DataVoalleController extends Controller
{
    public function index(Request $request)
    {

        $query = DB::table('data_voalle');

        // Inputs com busca não exata
        $terms = $request->only('id_contrato',
                                'nome_cliente',
                                'status',
                                'situacao',
                                'data_contrato',
                                'data_ativacao',
                                'vendedor',
                                'supervisor',
                                'data_cancelamento',
                                'plano');

        // Inputs com busca exata
        $terms_exactly = $request->only('id_contrato#',
                                        'nome_cliente#',
                                        'status#',
                                        'situacao#',
                                        'data_contrato#',
                                        'data_ativacao#',
                                        'vendedor#',
                                        'supervisor#',
                                        'data_cancelamento#',
                                        'plano#');

        // Busca os dados que correspondem a pesquisa
        foreach ($terms as $input => $valor) {
            if($valor) {
                $query->where($input, 'LIKE', '%'.$valor.'%');
            }
        }

        // Busca os dados exatos da pesquisa
        foreach ($terms_exactly as $input => $valor) {
            if($valor) {
                $query->where(explode('#', $input)[0], $valor);
            }
        }


        return response()->json($query->select('id', 'id_contrato', 'nome_cliente', 'status', 'situacao', 'data_contrato', 'data_ativacao',
                                                'conexao', 'vendedor', 'supervisor', 'data_cancelamento', 'plano')->limit(100)->get());

    }

    public function create()
    {
        //
    }

    public function store(Request $request)
    {
        //
    }

    public function show($id)
    {
        //
    }


    public function edit($id)
    {
        //
    }


    public function update(Request $request, $id)
    {
        //
    }

    public function destroy($id)
    {
        //
    }
}
