<?php

namespace App\Http\Controllers;

use App\Models\Collaborator;
use App\Models\DataVoalle;
use Carbon\Carbon;
use Illuminate\Http\Request;

class CollaboratorController extends Controller
{

    public function index()
    {
        //
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
