<?php /*

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Player extends Model
{
    use HasFactory;

    protected $fillable = [
        'nome_completo',
        'data_nascimento',
        'cpf',
        'email',
        'senha',
        'endereco_cobranca_cep',
        'endereco_cobranca_complemento',
        'endereco_cobranca_numero'
    ];
}*/

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Player extends Model
{
    use HasFactory;

    protected $fillable = [
        'nome_completo',
        'data_nascimento',
        'cpf',
        'email',
        'senha',
        'endereco_cobranca_cep',
        'endereco_cobranca_complemento',
        'endereco_cobranca_numero',
        'endereco_cobranca_logradouro', 
        'endereco_cobranca_bairro',     
        'endereco_cobranca_cidade',     
        'endereco_cobranca_estado',
        'saldo'                           
    ];

}

