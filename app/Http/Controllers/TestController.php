<?php

namespace App\Http\Controllers;

use App\Models\DataVoalle;
use Illuminate\Http\Request;

class TestController extends Controller
{
    public function index(Request $request)
    {
        $vendas = DataVoalle::where('vendedor', $request->header('vendedor'))->whereDate('data_ativacao', '>=', '01/05/2022')
                                ->whereDate('data_ativacao', '<=', '31/05/2022')->get();

        return response()->json($vendas);
    }
}
