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
use Illuminate\Http\Resources\Json\ResourceCollection;
use Illuminate\Support\Facades\DB;
use phpDocumentor\Reflection\Types\Resource_;
use function PHPUnit\Framework\isNull;

class TestController extends Controller
{
    public function index(Request $request)
    {
        $mcv = Collaborator::where('canal', 'mcv')->select('id')->get();

        $mcv->each(function ($item, $key) {
            $meta = Meta::create([
                'colaborador_id' => $item->id,
                'meta' => '60',
                'mes_competencia' => '08',
                'user_id' => 1
            ]);
        });
    }

}
