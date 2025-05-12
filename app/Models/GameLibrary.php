<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GameLibrary extends Model
{
    use HasFactory;

    protected $table = 'libraries';

    protected $fillable = [
        'user_id',
        'rawg_id',
        'status',
        'notes',
        'rating'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
