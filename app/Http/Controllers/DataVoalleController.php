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

        //return response()->json($request->all());

        // Inputs com busca não exata
        $terms = $request->only('id_contrato',
                                'nome_cliente',
                                'vendedor',
                                'supervisor',
                                'plano');

        $terms_multiple_status = $request->only('status');
        $terms_multiple_situattion = $request->only('situacao');

        // Inputs com busca exata
        $terms_exactly = $request->only('id_contrato#',
                                        'nome_cliente#',
                                        'status#',
                                        'situacao#',
                                        'vendedor#',
                                        'supervisor#',
                                        'plano#');

        // Inputs com busca em datas
        $terms_date = $request->only('data_contrato', 'data_contrato#',
                                        'data_ativacao', 'data_ativacao#',
                                        'data_cancelamento', 'data_cancelamento#');

        // Busca os dados que correspondem a pesquisa
        foreach ($terms as $input => $valor) {
            if($valor) {
                $query->where($input, 'LIKE', '%'.$valor.'%');
            }
        }

        foreach($terms_multiple_status as $input => $valor) {
            if($valor) {
                if(count($valor) > 1) {
                    foreach($valor as $vlr) {
                        $query->orWhere('status', $vlr);
                    }
                } else {
                    foreach($valor as $vlr) {
                        $query->where('status', $vlr);
                    }
                }
            }
        }

        foreach($terms_multiple_situattion as $input => $valor) {
            if($valor) {
                if(count($valor) > 1) {
                    foreach($valor as $vlr) {
                        $query->orWhere('situacao', $vlr);
                    }
                } else {
                    foreach($valor as $vlr) {
                        $query->where('situacao', $vlr);
                    }
                }
            }
        }

        // Busca os dados que correspondem a pesquisa de data
        foreach ($terms_date as $input => $valor) {
            if($valor) {
                if(str_contains($input, '#')) {
                    $query->whereDate(explode('#', $input)[0], '<=', date($valor));
                } else {
                    $query->whereDate($input, '>=', date($valor));
                }

            }
        }

        // Busca os dados exatos da pesquisa
        foreach ($terms_exactly as $input => $valor) {
            if($valor) {
                $query->where(explode('#', $input)[0], $valor);
            }
        }

        $query = $query->select('id', 'id_contrato', 'nome_cliente', 'status', 'situacao', 'data_contrato', 'data_ativacao',
            'conexao', 'vendedor', 'supervisor', 'data_cancelamento', 'plano')->limit(100)->get();

        for ($i = 0; $i < 5; $i++) {
            $this->contains_remove($query);
        }

        return response()->json($query);
    }

    public function getFilters()
    {
        $status = DataVoalle::select('status')->distinct()->orderBy('status', 'asc')->get();
        $situations = DataVoalle::select('situacao')->distinct()->orderBy('situacao', 'asc')->get();

        return response()->json([
            $status, $situations
        ]);
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

    public function contains_remove($query)
    {
        foreach($query as $id => $valor) {
            if(str_contains($valor->plano, 'FIDELIZADO')) {
                $valor->plano = explode('FIDELIZADO', $valor->plano)[0];
            } elseif (str_contains($valor->plano, 'TURBINADO')) {
                $valor->plano = explode('TURBINADO', $valor->plano)[0];
            } elseif (str_contains($valor->plano, '+')) {
                $valor->plano = explode('+', $valor->plano)[0];
            } elseif (str_contains($valor->plano, 'PROMOCAO')) {
                $valor->plano = explode('PROMOCAO', $valor->plano)[0];
            } elseif (str_contains($valor->plano, 'NÃO')) {
                $valor->plano = explode('NÃO', $valor->plano)[0];
            }
        }
    }
}
