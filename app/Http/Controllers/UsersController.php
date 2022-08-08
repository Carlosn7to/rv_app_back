<?php

namespace App\Http\Controllers;

use App\Models\Collaborator;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Nette\Utils\Random;
use PhpParser\Node\Scalar\String_;
use Psy\Util\Str;

class UsersController extends Controller
{

    public function index()
    {
        //
    }


    public function create()
    {

    }

    public function store(Request $request)
    {

        $id = $request->only('id');

        $collaborator = Collaborator::where('id', $id)->first();

        if(!isset($collaborator->nome)) {
            return response('Erro interno, contacte o setor responsável! Erro: #1-A', 403 );
        }

        $nameFull = $collaborator->nome;


        $name = explode(' ', $nameFull);

        $email = strtolower($name[0].".".$name[1]."@agetelecom.com.br");

        $password = \Illuminate\Support\Str::random(15);

        $user = User::create([
            'name' => $nameFull,
            'email' => $email,
            'password' => Hash::make($password)
        ]);

        if(isset($user->name)) {
            $collaborator->update([
                'tem_usuario' => 1,
                'status_usuario' => 'Ativo'
            ]);
            return response()->json([
                'user' => $email,
                'password' => $password,
                'msg' => 'Usuário criado com sucesso!'
            ]);
        } else {
            return response('Erro interno, contacte o setor responsável!', 403 );
        }
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
        if(!empty($id)) {
            $user = User::where('name', $id)->first();

            if(isset($user->name)) {
                $user = $user->delete();
                $collaborator = Collaborator::where('nome', $id)->first();
                $collaborator->update([
                    'status_usuario' => 'Inativo',
                    'tem_usuario' => 0
                ]);
                return response('Usuário inativado!', 203 );
            } else {
                return response('Usuário não encontrado! Erro: #2-A', 403 );
            }

        } else {
            return response('Erro interno, contacte o setor responsável! Erro: #1-AB', 403 );
        }
    }
}
