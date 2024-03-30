<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;
use App\Models\CarsRent;

class RentController extends Controller
{
    public function __construct(CarsRent $rent){
        $this->rent = $rent;
    }

    public function index(){
        $rents = CarsRent::all();

        return response()->json([
            'meta' => [
                'code' => 200,
                'status' => 'Success',
                'message' => 'All rented cars fetched!'
            ],
            'data' => [
                'total' =>  count($rents),
                'cars' => $rents
            ]
            ]);
    }

    public function detail(Request $request, $id){
        $validator = Validator::make(['id' => $id]);
    }
}
