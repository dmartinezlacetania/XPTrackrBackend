<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Game extends Model
{
    use HasFactory;

    protected $fillable = [
        'rawg_id',
        'name',
        'released',
        'rating',
        'background_image'
    ];
    
    public function libraries()
    {
        return $this->hasMany(GameLibrary::class, 'game_id');
    }
}
