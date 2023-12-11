
<?php 
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTransacaosTable extends Migration
{
    public function up()
    {
        Schema::create('transacaos', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('player_id');
            $table->foreign('player_id')->references('id')->on('players');
            $table->string('tipo'); // 'TRANSFERENCIA' ou 'DEPOSITO'
            $table->decimal('valor', 10, 2);
            $table->string('codigo_autorizacao');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('transacaos');
    }
}
