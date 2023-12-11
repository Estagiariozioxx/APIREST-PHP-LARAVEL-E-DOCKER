<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Transacao extends Model
{
    use HasFactory;

    
    protected $fillable = [
        'player_id',
        'tipo',
        'valor',
        'codigo_autorizacao'
    ];
}
