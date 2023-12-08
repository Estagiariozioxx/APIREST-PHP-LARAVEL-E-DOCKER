<?php

namespace App\Http\Controllers;

use App\Models\Player;

use Illuminate\Http\Request;

class PlayerController extends Controller{

    public function index(){
        $players = Player::all();
        return response()->json($players);

    }
    public function store(Request $request){
        $validateData=$request->validate([
            'name'=>'required|string',
            'email'=>'required|string',
        ]);
        $player = Player::create($validateData);
        return response()->json($player,201);

    }

    public function show(player $player){
        return response()->json($player);
    }

    public function update(Request $request, Player $player){
        $validateData=$request->validate([
            'name'=>'required|string',
            'email'=>'required|string',
        ]);
        $player->update($validateData);
        return response()->json($player,200);
    }
    public function destroy (Player $player){
        $player->delete();

        return response()->json(null,201);
    }

   

}