<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CarsReturn extends Model
{
    use HasFactory;

    protected $table = 'cars_returns';

    protected $fillable = [
        'user_id',
        'car_id',
        'return_date',
        'actual_return_date',
        'penalty_fee'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function car()
    {
        return $this->belongsTo(Cars::class);
    }
}
