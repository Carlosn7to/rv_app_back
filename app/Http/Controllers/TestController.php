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

        return Collaborator::where('nome', '<>', 'Amanda Andrade Brito')->get()->map(function (Collaborator $collaborator) {
            $collaborator->nome = mb_convert_case($collaborator->nome, MB_CASE_TITLE, 'UTF-8');
            return $collaborator;
        });

    }
}
