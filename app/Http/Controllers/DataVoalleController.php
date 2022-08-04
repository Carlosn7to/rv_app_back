<?php

namespace App\Http\Controllers;

use App\Models\Collaborator;
use App\Models\DataVoalle;
use App\Models\Meta;
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
        $year = $request->input('year');
        $month = $request->input('month');

        // verifica se o input veio com os dados do filtro, se não, tras os dados do mês atual
        if($year === null) {
            $year = Carbon::now()->format('Y');
        }
        if($month === null) {
            $month = Carbon::now()->format('m');
        }
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
            'plano')
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
            'plano')
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
        if(isset($topSale->plano)) {
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
        }

        // Validações dos campos recebidos
            if(isset($topSale->plano)) {
                $plan = $topSale->plano;
            } else {
                $plan = 'Nenhum plano encontrado';
            }

            if(isset($topSale->plano)) {
                $plan_qntd = $topSale->qntd;
            } else {
                $plan_qntd = 'Nenhum plano encontrado';
            }

        // Projeção de vendas

            // recupera o dia de hoje
            $today = Carbon::now()->format('d');

            // Conta quantos dias tem o mês
            $daysMonth = Carbon::now()->format('t');

            // Subtrai as datas para verificar a quantidade de dias restante
            $daysMissing = $daysMonth - $today;

        return response()->json([
            'sales' => $sales,
            'dashboard' => [
                'sales' => count($salesCount),
                'plan' => $plan,
                'plan_qntd' => $plan_qntd,
                'cancelled' => $cancelled,
                'stars' => $this->stars($username, $status, $month, $year, count($sales))
            ]
        ]);
    }

    public function stars($username, $status, $month, $year, $sales)
    {


        $plans = DataVoalle::select('vendedor', 'plano')->selectRaw('count(id) AS qntd')
            ->where('vendedor', $username)
            ->where('status', '<>', 'Inválida')
            ->whereMonth('data_ativacao','=', $month)
            ->whereYear('data_ativacao', '=', $year)
            ->groupBy('plano')
            ->orderBy('qntd', 'desc')
            ->distinct()->get();

        $channel = Collaborator::where('nome', $username)->select('id','canal')->first();
        $meta = Meta::where('colaborador_id', $channel->id)->where('mes_competencia', $month)->select('meta')->first();


        // Regra de negócio
        $stars = 0;
        $totalStars = 0;
        $priceStar = 0;
        $channel = $channel->canal;

        // Recuperando meta
        if(isset($meta->meta)) {
            $meta = $meta->meta;
        } else {
            $meta = 0;
        }

        // Variação no valor baseado na porcentagem da meta
        if($meta > 0) {
            $result = $sales / $meta * 100;
        } else {
            return "Colaborador sem meta definida";
        }

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
            } elseif ($channel === 'MCV') {

                if($result >= 60 && $result < 100) {
                    $priceStar = 0.90;
                } elseif($result >= 100 && $result < 120) {
                    $priceStar = 1.20;
                } elseif($result >= 120 && $result < 141) {
                    $priceStar = 2;
                } elseif($result >= 141) {
                    $priceStar = 4.5;
                }
            } elseif ($channel === 'PAP') {

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
            'meta' => round($result, 2),
            'projection' => $this->starsRule($stars, $priceStar, $username, $status, $month, $year, $sales)
        ]);
    }

    public function starsRule($stars, $priceStar, $username, $status, $month, $year, $sales)
    {

        $stars = $stars;
        $today = Carbon::now()->format('d');
        $daysMonth = Carbon::now()->format('t');
        $missing = $daysMonth - $today;
        $starsMissing = $missing * $stars;
        $result = $starsMissing / $today;
        $comission = $result * $priceStar;


        return response()->json([
            'today' => $today,
            'missing' => $missing,
            'stars' =>   number_format($result, 0),
            'comission' => $this->comissionRule($username, $status, $month, $year, $sales, number_format($result, 0))
        ]);

    }

    public function comissionRule($username, $status, $month, $year, $sales, $stars)
    {

        return "comission rule";

        $sales = $sales;
        $today = Carbon::now()->format('d');
        $daysMonth = Carbon::now()->format('t');
        $missing = $daysMonth - $today;
        $starsMissing = $missing * $sales;
        $resultSales = $starsMissing / $today;

        $plans = DataVoalle::select('vendedor', 'plano')->selectRaw('count(id) AS qntd')
            ->where('vendedor', $username)
            ->where('status', '<>', 'Inválida')
            ->whereMonth('data_ativacao','=', $month)
            ->whereYear('data_ativacao', '=', $year)
            ->groupBy('plano')
            ->orderBy('qntd', 'desc')
            ->distinct()->get();

        $channel = Collaborator::where('nome', $username)->select('id','canal')->first();
        $meta = Meta::where('colaborador_id', $channel->id)->where('mes_competencia', $month)->select('meta')->first();


        // Regra de negócio
        $totalStars = 0;
        $priceStar = 0;
        $channel = $channel->canal;

        // Recuperando meta
        if(isset($meta->meta)) {
            $meta = $meta->meta;
        } else {
            $meta = 0;
        }

        // Variação no valor baseado na porcentagem da meta
        if($meta > 0) {
            $result = $sales / $meta * 100;
        } else {
            return "Colaborador sem meta definida";
        }


        // Projetando meta baseado na regra de 3
        $missing = $daysMonth - $today;
        $metaMissing = $missing * $result;
        $result = $metaMissing / $today;

        // Aplicando remuneração variável
        if($channel === 'PJ') {

            if($result >= 70 && $result < 100) {
                $priceStar = 1.30;
            } elseif($result >= 100 && $result < 120) {
                $priceStar = 3;
            } elseif($result >= 120 && $result < 141) {
                $priceStar = 5;
            } elseif($result >= 141) {
                $priceStar = 7;
            }
        } elseif ($channel === 'MCV') {

            if($result >= 70 && $result < 100) {
                $priceStar = 0.90;
            } elseif($result >= 100 && $result < 120) {
                $priceStar = 1.20;
            } elseif($result >= 120 && $result < 141) {
                $priceStar = 2;
            } elseif($result >= 141) {
                $priceStar = 4.5;
            }
        } elseif ($channel === 'PAP') {

            if($result >= 70 && $result < 100) {
                $priceStar = 1.30;
            } elseif($result >= 100 && $result < 120) {
                $priceStar = 3;
            } elseif($result >= 120 && $result < 141) {
                $priceStar = 5;
            } elseif($result >= 141) {
                $priceStar = 7;
            }
        }

        return response()->json([
            'sales' => number_format($resultSales, 0),
            'meta' => number_format($result, 2),
            'comission' => number_format($stars * $priceStar, 2, ',', '.')
        ]);
    }

    public function create()
    {
        $query = 'SELECT DISTINCT
                    c.id as "id_contrato",
                    p.name AS "nome_cliente",
                    c.v_stage as "status",
                    c.v_status as "situacao",
                    c.date as "data_contrato",
                    caa.activation_date as "data_ativacao",
                    ac.user as "conexao",
                    c.amount AS "valor",
                    (SELECT name AS "vendedor" FROM erp.people p WHERE c.seller_1_id = p.id),
                    (SELECT name AS "supervisor" FROM erp.people p WHERE c.seller_2_id = p.id),
                    c.cancellation_date AS "data_cancelamento",
                    CASE
                        WHEN sp.title <> \'\' THEN sp.title
                        WHEN c.v_status = \'Cancelado\' THEN
                            CASE
                                WHEN cst.title is null THEN cst2.title
                                ELSE
                                    cst.title
                            END
                    END AS "plano"
                FROM
                    erp.contracts c
                LEFT JOIN
                    erp.contract_assignment_activations caa ON caa.contract_id = c.id
                LEFT JOIN
                    erp.authentication_contracts ac ON ac.contract_id = c.id
                LEFT JOIN
                    erp.people p ON p.id = c.client_id
                LEFT JOIN
                    erp.service_products sp ON ac.service_product_id = sp.id
                LEFT JOIN
                    erp.contract_service_tags cst ON cst.contract_id = c.id AND cst.title LIKE \'PLANO COMBO%\'
                LEFT JOIN
                    erp.contract_service_tags cst2 ON cst2.contract_id = c.id AND cst2.title LIKE \'PLANO%\' AND cst2.title NOT LIKE \'%COMBO%\'
                WHERE
                    caa.contract_id = c.id
                    OR
                    ac.user LIKE \'ALCL%\'';

        $salesVoalle = DB::connection('pgsql')->select($query);

        // Instanciando o banco de dados local
        $dataVoalle = new DataVoalle();

        set_time_limit(500);

        foreach($salesVoalle as $sale => $value) {
            $dataVoalle->firstOrCreate([
                'id_contrato' => $value->id_contrato,
            ], [
                'nome_cliente' => $value->nome_cliente,
                'status' => $value->status,
                'situacao' => $value->situacao,
                'data_contrato' => $value->data_contrato,
                'data_ativacao' => $value->data_ativacao,
                'conexao' => $value->conexao,
                'valor' => $value->valor,
                'vendedor' => $value->vendedor,
                'supervisor' => $value->supervisor,
                'data_cancelamento' => $value->data_cancelamento,
                'plano' => $value->plano,
            ]);
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
