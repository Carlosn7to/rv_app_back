<?php

namespace App\Http\Controllers;

use App\Models\DataVoalle;
use App\Models\Usuario;
use Illuminate\Http\Request;

class TestController extends Controller
{
    public function index(Request $request)
    {
        //$vendas = DataVoalle::where('vendedor', $request->header('vendedor'))->get();
        //return response()->json($vendas);

        $user = Usuario::all();

        return response()->json($user);
    }
}
