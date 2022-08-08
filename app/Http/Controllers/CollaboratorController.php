<?php

namespace App\Http\Controllers;

use App\Models\Collaborator;
use App\Models\DataVoalle;
use App\Models\Meta;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CollaboratorController extends Controller
{

    public function index()
    {
//        $collaborators = Collaborator::select('colaboradores.id',
//                                                'colaboradores.nome',
//                                                'colaboradores.funcao',
//                                                'colaboradores.canal',
//                                                'colaboradores.deleted_at')
//                                    ->selectRaw('(SELECT DISTINCT meta FROM meta_colaborador WHERE colaborador_id = colaboradores.id AND mes_competencia = \'08\') as Meta')
//                                    ->leftJoin('meta_colaborador','colaboradores.id', '=', 'meta_colaborador.colaborador_id' )
//                                    ->withTrashed()->distinct()->orderBy('colaboradores.id', 'desc')->get();

        $query = 'SELECT DISTINCT c.id, c.nome, c.funcao, c.canal, c.supervisor, c.tem_usuario, c.status_usuario,
                    (SELECT DISTINCT meta AS meta FROM meta_colaborador WHERE colaborador_id = c.id AND mes_competencia = \''.Carbon::now()->format('m').'\') AS meta,
                    c.deleted_at
                FROM colaboradores c
                LEFT JOIN meta_colaborador mc ON c.id = mc.colaborador_id';

        $collaborators = DB::connection('mysql')->select($query);

        foreach($collaborators as $c => $valor) {
            $valor->nome = mb_convert_case($valor->nome, MB_CASE_TITLE, 'UTF-8');
            $valor->funcao = mb_convert_case($valor->funcao, MB_CASE_TITLE, 'UTF-8');
            $valor->canal = mb_convert_case($valor->canal, MB_CASE_UPPER, 'UTF-8');
        }

        return response()->json($collaborators);
    }


    public function create()
    {

        $date = Carbon::parse('2022-05-01');

        // Instanciando
        $createVendors = new Collaborator();


        $supervisors = DataVoalle::select('supervisor')->where('supervisor', '<>', '')->where('data_contrato', '>=', $date)->distinct()->get();

        // Criando registros únicos
        foreach($supervisors as $supervisor => $valor){
            $createVendors->firstOrCreate([
                'nome' => $valor->supervisor,
            ], [
                'funcao' => 'supervisor'
            ]);
        }


        // Buscando vendedores únicos no banco de dados
        $vendors = DataVoalle::select('vendedor', 'supervisor')->where('vendedor', '<>', '')->where('data_contrato', '>=', $date)->distinct()->get();

        // Criando registros únicos
        foreach($vendors as $vendor => $valor){
            if($valor->supervisor === 'MULTI CANAL DE VENDAS') {
                $createVendors->firstOrCreate([
                    'nome' => $valor->vendedor,
                    ], [
                        'funcao' => 'vendedor',
                        'canal' => 'MCV'
                ]);
            } else {
                $createVendors->firstOrCreate([
                    'nome' => $valor->vendedor,
                ], [
                    'funcao' => 'vendedor'
                ]);
            }
        }

    }

    public function store(Request $request)
    {
        //
    }


    public function show($id)
    {
        $collaborator = Collaborator::select('id', 'nome', )->where('id', $id)->with('meta')->first();

        return $collaborator;
    }

    public function edit($id)
    {
        //
    }



    public function update(Request $request, $id)
    {

        $collaborator = Collaborator::findOrFail($id);

        $collaborator->update([
            'supervisor' => mb_convert_case($request->input('supervisor'), MB_CASE_TITLE, 'UTF-8'),
            'canal' => mb_convert_case($request->input('canal'), MB_CASE_UPPER, 'UTF-8'),
        ]);

        $meta = Meta::where('colaborador_id', $id)->where('mes_competencia', Carbon::now()->format('m'))->first();

        if(empty($meta)){

            $month = Carbon::now()->format('m');

            $meta = Meta::create([
                'colaborador_id' => $id,
                'meta' => $request->input('meta'),
                'mes_competencia' => $month,
                'user_id' => 1
            ]);
        } else {
            $meta->update([
                'meta' => $request->input('meta')
            ]);
        }

        if($collaborator) {
            if($meta) {
                return response()->json([
                    'msg' => 'Dados alterados com sucesso!',
                    'status' => 1
                ]);
            }
        } else {
            return response()->json([
                'msg' => 'Erro interno, tente novamente mais tarde!',
                'status' => 0
            ]);
        }
    }


    public function destroy($id)
    {
        //
    }
}
