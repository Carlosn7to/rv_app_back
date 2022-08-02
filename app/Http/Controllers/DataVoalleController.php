<?php

namespace App\Http\Controllers;

use App\Models\DataVoalle;
use Carbon\Carbon;
use Dflydev\DotAccessData\Data;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use PhpParser\Node\Expr\Array_;

class DataVoalleController extends Controller
{
    public function index(Request $request)
    {

        $query = DB::connection('mysql')->table('data_voalle');

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
            'conexao', 'vendedor', 'supervisor', 'data_cancelamento', 'plano')->where('vendedor', '<>', '')->where('supervisor', '<>', '')->limit(100)->get();

        for ($i = 0; $i < 5; $i++) {
            $this->contains_remove($query);
        }

        foreach($query as $item => $valor) {
            $valor->nome_cliente = mb_convert_case($valor->nome_cliente, MB_CASE_TITLE, 'UTF-8');
            $valor->vendedor = mb_convert_case($valor->vendedor, MB_CASE_TITLE, 'UTF-8');
            $valor->supervisor = mb_convert_case($valor->supervisor, MB_CASE_TITLE, 'UTF-8');
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

    public function getVendors(Request $request)
    {
        $query = DB::connection('mysql')->table('data_voalle');
        $terms = $request->only('vendedor');

        // Busca os dados que correspondem a pesquisa
        foreach ($terms as $input => $valor) {
            if($valor) {
                $query->where($input, 'LIKE', '%'.$valor.'%');
            }
        }

        $query = $query->select('vendedor')->where('vendedor', "<>", '')->distinct()->orderBy('vendedor', 'ASC')->get();

        foreach($query as $item => $valor) {
           $valor->vendedor = mb_convert_case($valor->vendedor, MB_CASE_TITLE, 'UTF-8');
        }

        return response()->json($query);
    }

    public function getSupervisors(Request $request)
    {
        $query = DB::connection('mysql')->table('data_voalle');
        $terms = $request->only('supervisor');

        // Busca os dados que correspondem a pesquisa
        foreach ($terms as $input => $valor) {
            if($valor) {
                $query->where($input, 'LIKE', '%'.$valor.'%');
            }
        }

        $query = $query->select('supervisor')->where('supervisor', "<>", '')->distinct()->orderBy('supervisor', 'ASC')->get();

        foreach($query as $item => $valor) {
            $valor->supervisor = trim(mb_convert_case($valor->supervisor, MB_CASE_TITLE, 'UTF-8'));
        }

        return response()->json($query);
    }

    public function getSupervisorData(Request $request)
    {
        $vendors = DataVoalle::select('vendedor')->where('supervisor', $request->input('supervisor'))->where('vendedor', '<>', '')->orderBy('vendedor', 'asc')
                                ->distinct()->get();

        foreach($vendors as $item => $valor) {
            $valor->vendedor = mb_convert_case($valor->vendedor, MB_CASE_TITLE, 'UTF-8');
        }

        return response()->json($vendors);
    }

    public function getSupervisorAmount(Request $request)
    {
        $supervisor = 'Keila Jaqueline da Silva';

        $query = 'SELECT SUM(valor) as "Valor", MONTHNAME(data_contrato) AS "Mês", YEAR(data_contrato) as "Ano"
                    FROM data_voalle WHERE supervisor = "'.$supervisor.'"
                    AND data_contrato >= 2022-01-01 GROUP BY YEAR(data_contrato), MONTH(data_contrato)';

        $query = DB::connection('mysql')->select($query);

        foreach ($query as $field => $valor) {
            $valor->Valor = $valor->Valor/100;
        }

        $amount = array();
        foreach ($query as $row => $valor) {
            $amount[] = $valor->Valor;
        }

        $month = array();
        foreach ($query as $row => $valor) {
            $month[] = $valor->Mês;
        }


        return response()->json([
            'data' => $query,
            'amount' => $amount,
            'month' => $month
        ]);
    }

    public function getSupervisorTeam()
    {
        $data = Carbon::now();
        $data = date('Y/m/d', strtotime('-35 days', strtotime($data)));
        $supervisor = 'Keila Jaqueline da Silva';

        $active = DataVoalle::whereDate('data_contrato', '>=', $data)->where('supervisor', $supervisor)->select('vendedor')->distinct()->orderBy('vendedor')->get();

        $inactive = DB::connection('mysql')->table('data_voalle');

        foreach($active as $vendor => $name) {
            $inactive->where('vendedor', '<>', "$name->vendedor");
        }

        $inactive = $inactive->whereDate('data_contrato', '<=', $data)->where('vendedor', '<>', '')->where('supervisor', $supervisor)
                    ->select('vendedor')->distinct()->orderBy('vendedor')->get();

        return response()->json([
            'active' => count($active),
            'inative' => count($inactive)
        ]);
    }

    public function filterSalesVendor(Request $request)
    {
        $year = $request->only('year');
        $month = $request->only('month');
        $status = [];
        $username = $request->header('username');

        $sales = DataVoalle::select('id',
            'id_contrato',
            'nome_cliente',
            'status',
            'situacao',
            'data_contrato',
            'data_ativacao',
            'data_cancelamento',
            'vendedor',
            'plano',
            'estrela')
            ->where('vendedor', $username)
            ->whereMonth('data_ativacao','=', $month)
            ->whereYear('data_ativacao', '=', $year)
            ->where('status', $status)->get();

        foreach($sales as $sale => $valor) {
            if($valor->situacao === 'Cancelado') {
                $dateActive = Carbon::parse($valor->data_ativacao);
                $dateCancel = Carbon::parse($valor->data_cancelamento);
                if($dateActive->diffInDays($dateCancel) < 7) {
                    $updateStatus = DataVoalle::where('id', $valor->id)->first();

                    $updateStatus->update([
                        'status' => 'Inválida'
                    ]);
                }
            }
        }

        $salesCount = DataVoalle::select('id',
            'id_contrato',
            'nome_cliente',
            'status',
            'situacao',
            'data_contrato',
            'data_ativacao',
            'data_cancelamento',
            'vendedor',
            'plano',
            'estrela')
            ->where('vendedor', $username)
            ->where('status', '<>', 'Inválida')
            ->whereMonth('data_ativacao','=', $month)
            ->whereYear('data_ativacao', '=', $year)
            ->where('status', $status)->get();

        $topSale = DataVoalle::select('plano')->selectRaw('count(id) AS qntd')
                                ->where('vendedor', $username)
                                ->where('status', $status)
                                ->whereMonth('data_ativacao','=', $month)
                                ->whereYear('data_ativacao', '=', $year)
                                ->groupBy('plano')
                                ->orderBy('qntd', 'desc')
                                ->limit(1)->distinct()->first();

        $cancelled = DataVoalle::select('plano')->where('vendedor', $username)
                                    ->whereMonth('data_ativacao', '=', $month)
                                    ->whereYear('data_ativacao', '=', $year)
                                    ->where('status', 'Cancelado')->count();

        //limpeza da string plano array
        for($i = 0; $i < 5; $i++) {
            $this->contains_remove($sales);
        }

        // limpeza da string plano
        for($i = 0; $i < 5; $i++) {
                if(str_contains($topSale->plano, 'FIDELIZADO')) {
                    $topSale->plano = explode('FIDELIZADO', $topSale->plano)[0];
                } elseif (str_contains($topSale->plano, 'TURBINADO')) {
                    $topSale->plano = explode('TURBINADO', $topSale->plano)[0];
                } elseif (str_contains($topSale->plano, '+')) {
                    $topSale->plano = explode('+', $topSale->plano)[0];
                } elseif (str_contains($topSale->plano, 'PROMOCAO')) {
                    $topSale->plano = explode('PROMOCAO', $topSale->plano)[0];
                } elseif (str_contains($topSale->plano, 'NÃO')) {
                    $topSale->plano = explode('NÃO', $topSale->plano)[0];
                } elseif(str_contains($topSale->plano, 'EMPRESARIAL')) {
                    $topSale->plano = explode('EMPRESARIAL', $topSale->plano)[1];
                } elseif(str_contains($topSale->plano, 'PLANO')) {
                    $topSale->plano = explode('PLANO', $topSale->plano)[1];
                }
        }



        return response()->json([
            'sales' => $sales,
            'dashboard' => [
                'sales' => count($salesCount),
                'plan' => $topSale->plano,
                'plan_qntd' => $topSale->qntd,
                'cancelled' => $cancelled,
                'stars' => $this->stars($username, $status, $month, $year, count($sales))
            ],
        ]);
    }

    public function stars($username, $status, $month, $year, $sales)
    {

        $plans = DataVoalle::select('plano')->selectRaw('count(id) AS qntd')
            ->where('vendedor', $username)
            ->where('status', '<>', 'Inválida')
            ->whereMonth('data_ativacao','=', $month)
            ->whereYear('data_ativacao', '=', $year)
            ->groupBy('plano')
            ->orderBy('qntd', 'desc')
            ->distinct()->get();

        // Regra de negócio
        $stars = 0;
        $totalStars = 0;
        $priceStar = 0;
        $meta = 15;
        $channel = 'PJ';
        // Variação no valor baseado na porcentagem da meta

            // Descobrindo a porcentagem de vendas sobre a meta
            $result = $sales / $meta * 100;


            // Aplicando remuneração variável
            if($channel === 'PJ') {
                if($result >= 60 && $result < 100) {
                    $priceStar = 1.30;
                } elseif($result >= 100 && $result < 120) {
                    $priceStar = 3;
                } elseif($result >= 120 && $result < 141) {
                    $priceStar = 5;
                } elseif($result >= 141) {
                    $priceStar = 7;
                }
            }

        //limpeza da string plano array
        for($i = 0; $i < 5; $i++) {
            $this->contains_remove($plans);
        }

        foreach($plans as $plan => $valor){
            $valor->plano = trim($valor->plano);
            if($valor->plano === 'PLANO EMPRESARIAL 600 MEGA') {
                $stars += $valor->qntd*9;
            } elseif ($valor->plano === 'PLANO EMPRESARIAL 800 MEGA') {
                $stars += $valor->qntd*17;
            } elseif ($valor->plano === 'PLANO EMPRESARIAL 1 GIGA') {
                $stars += $valor->qntd*35;
            } elseif ($valor->plano === 'PLANO 240 MEGA') {
                $stars += $valor->qntd*9;
            } elseif ($valor->plano === 'PLANO 120 MEGA') {
                $stars += $valor->qntd*7;
            } elseif ($valor->plano === 'PLANO 740 MEGA') {
                $stars += $valor->qntd*25;
            } elseif ($valor->plano === 'PLANO 480 MEGA') {
                $stars += $valor->qntd*15;
            } elseif ($valor->plano === 'PLANO EMPRESARIAL  600 MEGA') {
                $stars += $valor->qntd*9;
            }
        }

        return response()->json([
            'comission' => number_format($priceStar * $stars, 2),
            'stars' => $stars,
            'price' => number_format($priceStar, 2),
            'meta' => round($result, 2)
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
