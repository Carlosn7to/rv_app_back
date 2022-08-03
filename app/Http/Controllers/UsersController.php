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

        $id = 89;

        $collaborator = Collaborator::where('id', $id)->first();

        $nameFull = $collaborator->nome;

        $name = explode(' ', $nameFull);

        $email = strtolower($name[0].".".$name[1]."@agetelecom.com.br");

        $password = 'kIuDA4QTghMh';

        $user = User::create([
            'name' => $nameFull,
            'email' => $email,
            'password' => Hash::make($password)
        ]);
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
