<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CarsRent extends Model
{
    use HasFactory;

    protected $table = 'cars_rent';

    protected $fillable = [
        'user_id',
        'car_id',
        'start_date',
        'end_date',
        'amount',
        'price'
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
