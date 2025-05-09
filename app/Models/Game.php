<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Game extends Model
{
    //
    // Define the table associated with the model
    protected $table = 'games';
    // Define the primary key associated with the table
    protected $primaryKey = 'id';
    // Define the attributes that are mass assignable
    protected $fillable = [
        'name',
        'description',
        'image',
        'platforms',
        'release_date',
        'rating',
    ];
    // Define the attributes that should be cast to native types
    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];
}
