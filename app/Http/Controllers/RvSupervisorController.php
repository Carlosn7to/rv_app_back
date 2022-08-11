<?php

namespace App\Http\Controllers;

use App\Models\Collaborator;
use App\Models\DataVoalle;
use App\Models\Meta;
use Carbon\Carbon;
use Illuminate\Http\Request;

class RvSupervisorController extends Controller
{
    public function index(Request $request)
    {

        $month = '06';
        $year = '2022';
        $typeCollaborator = 'supervisor';

        $sales = DataVoalle::select('id',
            'status',
            'situacao',
            'data_ativacao',
            'data_cancelamento')
            ->whereMonth('data_ativacao','=', $month)
            ->whereYear('data_ativacao', '=', $year)
            ->get();

        foreach($sales as $sale => $valor) {
            if($valor->situacao === 'Cancelado') {
                $dateActive = Carbon::parse($valor->data_ativacao);
                $dateCancel = Carbon::parse($valor->data_cancelamento);
                if($dateActive->diffInDays($dateCancel) <= 7) {
                    $updateStatus = DataVoalle::where('id', $valor->id)->first();

                    $updateStatus->update([
                        'status' => 'Inválida'
                    ]);
                }
            }
        }

        $supervisors = DataVoalle::select($typeCollaborator)
            ->whereMonth('data_ativacao', '=', $month)
            ->whereYear('data_ativacao', '=', $year)
            ->where('status', '<>', 'inválida')
            ->where($typeCollaborator, '<>', '')
            ->where('supervisor', '<>', 'BASE')
            ->where('supervisor', '<>', 'Ivaldo Moreira Pereira de Souza')
            ->with(['plans_supervisor' => function($q) use($month, $year) {
                $q->whereMonth('data_ativacao', $month)->whereYear('data_ativacao', $year);
            }])
            ->distinct()->orderBy($typeCollaborator, 'asc')->get();

        $array = [];

        foreach($supervisors as $sup => $value) {

            $name = $value->supervisor;
            $channel = $this->channel($name);
            $meta = $this->meta($name, $month, $year);
            $plans = $this->plans($value->plans_supervisor);
            $qntd_plans = $this->qntd_plans($value->plans_supervisor);
            $percent_meta = $this->percent_meta($meta, $qntd_plans);
            $stars = $this->stars($meta, $name, $month, $year);
            $price_stars = $this->price_stars($month, $name, $meta, $qntd_plans);
            $cancelleds = $this->cancelleds($name, $month, $year);
            $comission = $stars * $price_stars;


            $array[] = [
                'name' => $name,
                'channel' => $channel,
                'meta' => $meta,
                'percent_meta' => $percent_meta,
                'qntd_plans' => $qntd_plans,
                'plans' => $plans,
                'cancelled' => $cancelleds,
                'stars' => $stars,
                'price_stars' => $price_stars,
                'comission' => $comission
            ];
        }

        return response()->json($array);
    }

    public function channel($name)
    {
        $channel = Collaborator::where('nome', $name)->select('canal')->first();

        if(isset($channel->canal)) {
            return $channel->canal;
        }

        return '';
    }

    public function plans($plans_supervisor)
    {

        $plans = [];

        foreach($plans_supervisor as $plan => $value) {
            $plans[] = $value->plano;
        }

        return $plans;
    }

    public function qntd_plans($plans_supervisor)
    {
        $qntd = 0;

        foreach($plans_supervisor as $plan => $value) {
            $qntd += 1;
        }

        return $qntd;
    }

    public function price_stars($month, $name, $meta, $qntd)
    {

        //$channel = Collaborator::where('nome', $name)->select('canal')->first();

        $channel = 'LIDER';
        $priceStar = 0;

        if($meta > 0) {
            $result = $qntd / $meta * 100;
        } else {
            return $priceStar;
        }

        if($month >= '08') {
            $minMeta = 60;
        } elseif ($month < '08') {
            $minMeta = 70;
        }

        if($channel === 'PJ') {

            if($result >= $minMeta && $result < 100) {
                $priceStar = 1.30;
            } elseif($result >= 100 && $result < 120) {
                $priceStar = 3;
            } elseif($result >= 120 && $result < 141) {
                $priceStar = 5;
            } elseif($result >= 141) {
                $priceStar = 7;
            }
        } elseif ($channel === 'MCV') {

            if($result >= $minMeta && $result < 100) {
                $priceStar = 0.90;
            } elseif($result >= 100 && $result < 120) {
                $priceStar = 1.20;
            } elseif($result >= 120 && $result < 141) {
                $priceStar = 2;
            } elseif($result >= 141) {
                $priceStar = 4.5;
            }
        } elseif ($channel === 'PAP') {

            if($result >= $minMeta && $result < 100) {
                $priceStar = 1.30;
            } elseif($result >= 100 && $result < 120) {
                $priceStar = 3;
            } elseif($result >= 120 && $result < 141) {
                $priceStar = 5;
            } elseif($result >= 141) {
                $priceStar = 7;
            }
        } elseif($channel === 'LIDER') {
            if($result >= $minMeta && $result < 100) {
                $priceStar = 0.25;
            } elseif($result >= 100 && $result < 120) {
                $priceStar = 0.40;
            } elseif($result >= 120 && $result < 141) {
                $priceStar = 0.80;
            } elseif($result >= 141) {
                $priceStar = 1.30;
            }
        }

        return $priceStar;
    }

    public function stars($meta, $name, $month, $year)
    {
        // atributos
        $stars = 0;

        $plans = DataVoalle::where('supervisor', $name)
            ->whereMonth('data_ativacao', $month)
            ->whereYear('data_ativacao', $year)
            ->where('status', '<>', 'inválida')
            ->select('plano')->get();


        foreach($plans as $plan => $valor) {
            $valor = $this->sanitize_plan($valor);
            $valor = trim($valor);
            if($valor === 'PLANO EMPRESARIAL 600 MEGA') {
                $stars += 9;
            } elseif ($valor === 'PLANO EMPRESARIAL 800 MEGA') {
                $stars += 17;
            } elseif ($valor === 'PLANO EMPRESARIAL 1 GIGA') {
                $stars += 35;
            } elseif ($valor === 'PLANO 1 GIGA') {
                $stars += 35;
            } elseif ($valor === 'PLANO 240 MEGA') {
                $stars += 9;
            } elseif ($valor === 'PLANO 120 MEGA') {
                $stars += 7;
            } elseif ($valor === 'PLANO 740 MEGA') {
                $stars += 25;
            } elseif ($valor === 'PLANO 480 MEGA') {
                $stars += 15;
            } elseif ($valor === 'PLANO EMPRESARIAL  600 MEGA') {
                $stars += 9;
            } elseif ($valor === 'PLANO 400 MEGA') {
                $stars += 15;
            } elseif ($valor === 'PLANO 800 MEGA') {
                $stars += 25;
            } elseif ($valor === 'PLANO 960 MEGA') {
                $stars += 35;
            } elseif ($valor === 'PLANO 720 MEGA') {
                $stars += 25;
            }

        }


        return $stars;
    }

    public function meta($name, $month, $year)
    {
        $collaborator = Collaborator::where('nome', $name)->select('id')->first();

        if(isset($collaborator->id)) {

            $meta = Meta::where('colaborador_id', $collaborator->id)
                ->where('mes_competencia', $month)
                ->select('meta')->first();
            if(isset($meta->meta)){
                $meta = $meta->meta;
            } else {
                $meta = 0;
            }
        } else {
            $meta = 0;
        }
        return $meta;
    }

    public function percent_meta($meta, $qntd)
    {
        $result = 0;

        if($meta > 0) {
            $result = $qntd / $meta * 100;
        }

        return number_format($result, 2);
    }

    public function cancelleds($name, $month, $year)
    {
        return 0;
    }

    public function sanitize_plan($valor)
    {
        for($i = 0; $i < 5; $i++) {
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

        return $valor->plano;
    }
}
