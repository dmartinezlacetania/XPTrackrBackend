<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Comment extends Model
{
    //
    // Define the table associated with the model
    protected $table = 'comments';
    // Define the primary key associated with the table
    protected $primaryKey = 'id';
    // Define the attributes that are mass assignable
    protected $fillable = [
        'user_id',
        'game_id',
        'comment',
    ];
    // Define the attributes that should be cast to native types
    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];
}
