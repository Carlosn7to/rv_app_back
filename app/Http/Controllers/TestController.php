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

//        $nameCollaborator = $request->input('name');
//
//        $collaborator = Collaborator::where('nome', $nameCollaborator)->first();
//
//        $collaborator_id = $collaborator->id;
//        $meta = $request->input('meta');
//        $month = '06';
//        $user_id = 1;
//
//        $meta_collaborator = Meta::create([
//            'colaborador_id' => $collaborator_id,
//            'meta' => $meta,
//            'mes_competencia' => $month,
//            'user_id' => $user_id
//        ]);
//
//        return $meta_collaborator;


//        $nameSupervisor = $request->input('name');
//
//        $collaborator = Collaborator::where('canal', 'pj')->where('funcao','vendedor')->select('id')->get();
//
//        $meta = new Meta();
//
//        foreach($collaborator as $item => $value){
//            $meta->create([
//                'colaborador_id' => $value->id,
//                'meta' => 15,
//                'mes_competencia' => '06',
//                'user_id' => 1
//            ]);
//        }
    }
}
