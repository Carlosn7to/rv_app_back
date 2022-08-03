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
            $table->string('status',50)->nullable();
            $table->string('situacao',50)->nullable();
            $table->date('data_contrato')->nullable();
            $table->date('data_ativacao')->nullable();
            $table->string('conexao',50)->nullable();
            $table->double('valor')->nullable();
            $table->string('vendedor',255)->nullable();
            $table->string('supervisor',255)->nullable();
            $table->date('data_cancelamento')->nullable();
            $table->string('plano',255)->nullable();
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
