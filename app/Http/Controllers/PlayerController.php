<?php

namespace App\Http\Controllers;

use App\Models\Player;
use App\Models\Transacao;
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

    public function store(Request $request)
    {
        try{
            $validatedData = $request->validate([
                'nome_completo' => 'required|string',
                'data_nascimento' => 'required|date',
                'cpf' => 'required|string|unique:players',
                'email' => 'required|string|unique:players',
                'senha' => 'required|string',
                'endereco_cobranca_cep' => 'required|string',
                'endereco_cobranca_complemento' => 'nullable|string',
                'endereco_cobranca_numero' => 'required|string',
            ]);
            $cepData = Http::get("https://viacep.com.br/ws/{$validatedData['endereco_cobranca_cep']}/json/")->json();

            if (isset($cepData['logradouro'])) {
                $validatedData['endereco_cobranca_logradouro'] = $cepData['logradouro'];
            }
            if (isset($cepData['bairro'])) {
                $validatedData['endereco_cobranca_bairro'] = $cepData['bairro'];
            }
            if (isset($cepData['localidade'])) {
                $validatedData['endereco_cobranca_cidade'] = $cepData['localidade'];
            }
            if (isset($cepData['uf'])) {
                $validatedData['endereco_cobranca_estado'] = $cepData['uf'];
            }

            $validatedData['senha'] = Hash::make($validatedData['senha']);
            
            $player = Player::create($validatedData);

            return response()->json($player, 201);
        }catch (\Exception $e) {
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
        try {
            $validatedData = $request->validate([
                'nome_completo' => 'required|string',
                'data_nascimento' => 'required|date',
                'cpf' => 'required|string|unique:players,cpf,' . $player->id,
                'email' => 'required|string|unique:players,email,' . $player->id,
                'senha' => 'required|string',
                'endereco_cobranca_cep' => 'required|string',
                'endereco_cobranca_complemento' => 'nullable|string',
                'endereco_cobranca_numero' => 'required|string',
            ]);
    
            $cepData = Http::get("https://viacep.com.br/ws/{$validatedData['endereco_cobranca_cep']}/json/")->json();
    
            if (isset($cepData['logradouro'])) {
                $validatedData['endereco_cobranca_logradouro'] = $cepData['logradouro'];
            }
            if (isset($cepData['bairro'])) {
                $validatedData['endereco_cobranca_bairro'] = $cepData['bairro'];
            }
            if (isset($cepData['localidade'])) {
                $validatedData['endereco_cobranca_cidade'] = $cepData['localidade'];
            }
            if (isset($cepData['uf'])) {
                $validatedData['endereco_cobranca_estado'] = $cepData['uf'];
            }
    
            $validatedData['senha'] = Hash::make($validatedData['senha']);
    
            $player->update($validatedData);
    
            return response()->json($player, 200);
        } catch (\Exception $e) {
            Log::error('Error updating player:', ['error' => $e->getMessage()]);
            return response()->json(['error' => 'Internal Server Error'], 500);
        }
    }
    

    public function destroy(Player $player)
    {
        $player->delete();

        return response()->json(null, 204);
    }


    public function depositar(Request $request, Player $player)
    {
        $validatedData = $request->validate([
            'valor' => 'required|numeric|min:0.01',
        ]);

        $codigoAutorizacao = 'DEP' . str_pad(mt_rand(1, 9999), 4, '0', STR_PAD_LEFT);

        $player->saldo += $validatedData['valor'];
        $player->save();

        Transacao::create(['player_id' => $player->id, 'tipo' => 'DEPOSITO', 'valor' => $validatedData['valor'], 'codigo_autorizacao' => $codigoAutorizacao]);

        return response()->json(['mensagem' => 'Depósito realizado com sucesso', 'codigo_autorizacao' => $codigoAutorizacao], 200);
    }


    public function transferir(Request $request, Player $origemPlayer, Player $destinoPlayer)
    {
        $validatedData = $request->validate([
            'valor' => 'required|numeric|min:0.01',
        ]);

        $codigoAutorizacao = 'TRANSF' . str_pad(mt_rand(1, 9999), 4, '0', STR_PAD_LEFT);

        if ($origemPlayer->saldo < $validatedData['valor']) {
            return response()->json(['erro' => 'Saldo insuficiente'], 400);
        }

        $origemPlayer->saldo -= $validatedData['valor'];
        $destinoPlayer->saldo += $validatedData['valor'];

        $origemPlayer->save();
        $destinoPlayer->save();

         Transacao::create(['player_id' => $origemPlayer->id, 'tipo' => 'TRANSFERENCIA', 'valor' => -$validatedData['valor'], 'codigo_autorizacao' => $codigoAutorizacao]);
         Transacao::create(['player_id' => $destinoPlayer->id, 'tipo' => 'TRANSFERENCIA', 'valor' => $validatedData['valor'], 'codigo_autorizacao' => $codigoAutorizacao]);

        return response()->json(['mensagem' => 'Transferência realizada com sucesso', 'codigo_autorizacao' => $codigoAutorizacao], 200);
    }


}
