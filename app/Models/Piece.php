<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Piece extends Model
{
    protected $fillable = [
        'block_id',
        'name',
        'theoretical_weight',
        'real_weight',
        'weight_difference',
        'status',
        'manufactured_at'
    ];

    public function block()
    {
        return $this->belongsTo(Block::class);
    }
}