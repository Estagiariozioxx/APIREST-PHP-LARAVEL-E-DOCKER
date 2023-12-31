<?php 
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('players', function (Blueprint $table) {
            $table->id();
            $table->string('nome_completo');
            $table->date('data_nascimento');
            $table->string('cpf')->unique();
            $table->string('email')->unique();
            $table->string('senha');
            $table->string('endereco_cobranca_cep');
            $table->string('endereco_cobranca_complemento')->nullable();
            $table->string('endereco_cobranca_numero');
            $table->string('endereco_cobranca_logradouro')->nullable();
            $table->string('endereco_cobranca_bairro')->nullable();
            $table->string('endereco_cobranca_cidade')->nullable();
            $table->string('endereco_cobranca_estado')->nullable();
            $table->decimal('saldo', 10, 2)->default(0.00);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('players');
    }
};
