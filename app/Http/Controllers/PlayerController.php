<?php

namespace App\Http\Controllers;

use App\Models\Player;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class PlayerController extends Controller
{
    public function index()
    {
        $players = Player::all();
        return response()->json($players);
    }

    /*public function store(Request $request)
    {
        $validatedData = $request->validate([
            'nome_completo' => 'required|string',
            'data_nascimento' => 'required|date',
            'cpf' => 'required|string|unique:players',
            'email' => 'required|string|unique:players',
            'senha' => 'required|string',
            'endereco_cobranca.cep' => 'required|string',
            'endereco_cobranca.complemento' => 'nullable|string',
            'endereco_cobranca.numero' => 'required|string',
        ]);
        /*

        $cepData = Http::get("https://viacep.com.br/ws/{$validatedData['endereco_cobranca']['cep']}/json/")->json();
        $validatedData['endereco_cobranca']['logradouro'] = $cepData['logradouro'];
        $validatedData['endereco_cobranca']['bairro'] = $cepData['bairro'];
        $validatedData['endereco_cobranca']['cidade'] = $cepData['localidade'];
        $validatedData['endereco_cobranca']['estado'] = $cepData['uf'];

       // $validatedData['senha'] = Hash::make($validatedData['senha']);
        
        $player = Player::create($validatedData);

        return response()->json($player, 201);
    }*/
    public function store(Request $request)
    {
        try {
            $validatedData = $request->validate([
                'nome_completo' => 'required|string',
                'data_nascimento' => 'required|string',
                'cpf' => 'required|string|unique:players',
                'email' => 'required|string|unique:players',
                'senha' => 'required|string',
                'endereco_cobranca.cep' => 'required|string',
                'endereco_cobranca.complemento' => 'nullable|string',
                'endereco_cobranca.numero' => 'required|string',
            ]);

            Log::info('Validated Data:', $validatedData);

            $player = Player::create($validatedData);

            Log::info('Player created:', $player);

            return response()->json($player, 201);
        } catch (\Exception $e) {
            Log::error('Error creating player:', ['error' => $e->getMessage()]);
            return response()->json(['error' => 'Internal Server Error'], 500);
        }
    }


    public function show(Player $player)
    {
        return response()->json($player);
    }

    public function update(Request $request, Player $player)
    {
        $validatedData = $request->validate([
            'nome_completo' => 'required|string',
            'data_nascimento' => 'required|date',
            'cpf' => 'required|string|unique:players,cpf,'.$player->id,
            'email' => 'required|email|unique:players,email,'.$player->id,
            'endereco_cobranca.cep' => 'required|string',
            'endereco_cobranca.complemento' => 'nullable|string',
            'endereco_cobranca.numero' => 'required|string',
        ]);

        // Integração com a API do ViaCep para completar os dados do endereço de cobrança
        $cepData = Http::get("https://viacep.com.br/ws/{$validatedData['endereco_cobranca']['cep']}/json/")->json();
        $validatedData['endereco_cobranca']['logradouro'] = $cepData['logradouro'];
        $validatedData['endereco_cobranca']['bairro'] = $cepData['bairro'];
        $validatedData['endereco_cobranca']['cidade'] = $cepData['localidade'];
        $validatedData['endereco_cobranca']['estado'] = $cepData['uf'];

        $player->update($validatedData);

        return response()->json($player, 200);
    }

    // Remove the specified resource from storage.
    public function destroy(Player $player)
    {
        $player->delete();

        return response()->json(null, 204);
    }

        // Adicione este método ao PlayerController
    public function depositar(Request $request, Player $player)
    {
        $validatedData = $request->validate([
            'valor' => 'required|numeric|min:0.01',
        ]);

        // Gere o código de autorização no padrão DEP0000XXXX
        $codigoAutorizacao = 'DEP' . str_pad(mt_rand(1, 9999), 4, '0', STR_PAD_LEFT);

        // Atualize o saldo do jogador
        $player->saldo += $validatedData['valor'];
        $player->save();

        // Crie um registro de transação (opcional)
        // Transaction::create(['player_id' => $player->id, 'tipo' => 'DEPOSITO', 'valor' => $validatedData['valor'], 'codigo_autorizacao' => $codigoAutorizacao]);

        return response()->json(['mensagem' => 'Depósito realizado com sucesso', 'codigo_autorizacao' => $codigoAutorizacao], 200);
    }
    // Adicione este método ao PlayerController
    public function transferir(Request $request, Player $origemPlayer, Player $destinoPlayer)
    {
        $validatedData = $request->validate([
            'valor' => 'required|numeric|min:0.01',
        ]);

        // Gere o código de autorização no padrão TRANSF0000XXXX
        $codigoAutorizacao = 'TRANSF' . str_pad(mt_rand(1, 9999), 4, '0', STR_PAD_LEFT);

        // Verifique se há saldo suficiente na conta de origem
        if ($origemPlayer->saldo < $validatedData['valor']) {
            return response()->json(['erro' => 'Saldo insuficiente'], 400);
        }

        // Atualize os saldos dos jogadores
        $origemPlayer->saldo -= $validatedData['valor'];
        $destinoPlayer->saldo += $validatedData['valor'];

        $origemPlayer->save();
        $destinoPlayer->save();

        // Crie registros de transação (opcional)
        // Transaction::create(['player_id' => $origemPlayer->id, 'tipo' => 'TRANSFERENCIA', 'valor' => -$validatedData['valor'], 'codigo_autorizacao' => $codigoAutorizacao]);
        // Transaction::create(['player_id' => $destinoPlayer->id, 'tipo' => 'TRANSFERENCIA', 'valor' => $validatedData['valor'], 'codigo_autorizacao' => $codigoAutorizacao]);

        return response()->json(['mensagem' => 'Transferência realizada com sucesso', 'codigo_autorizacao' => $codigoAutorizacao], 200);
    }


}
