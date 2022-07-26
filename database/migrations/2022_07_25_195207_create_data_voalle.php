<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('data_voalle', function (Blueprint $table) {
            $table->id();
            $table->string('id_contrato',50);
            $table->string('nome_cliente',255);
            $table->string('status',50);
            $table->string('situacao',50);
            $table->string('data_contratotemp');
            $table->date('data_contrato');
            $table->string('data_ativacaotemp');
            $table->date('data_ativacao');
            $table->string('conexao',50);
            $table->string('vendedor',255);
            $table->string('supervisor',255);
            $table->string('data_cancelamentotemp');
            $table->date('data_cancelamento');
            $table->string('plano',255);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('data_voalle');
    }
};
