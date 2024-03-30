<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;
use App\Models\Cars;
use App\Models\CarsRent;
use Carbon\Carbon;

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

    // public function detail(Request $request, $id){
    //     $validator = Validator::make(['id' => $id]);
    // }

    public function store(Request $request){
        $validator = Validator::make($request->all(), [
            'user_id' => 'required|exists:users,id', 
            'car_id' => 'required|exists:cars,id', 
            'start_date' => 'required|date|after:now', 
            'end_date' => 'required|date|after:start_date', 
            'amount' => 'required|integer|min:0',
            'price' => 'required|integer|min:0', 
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed.',
                'errors' => $validator->errors()
            ], 422);
        }

        $car = Cars::find($request->input('car_id'));
        $duration = Carbon::parse($request->input('start_date'))->diffInDays(Carbon::parse($request->input('end_date')));
        $price = ($duration * $car->tariff) * $request->input('amount');

        $carRent = CarsRent::create([
            'user_id' => $request->input('user_id'),
            'car_id' => $request->input('car_id'),
            'start_date' => $request->input('start_date'),
            'end_date' => $request->input('end_date'),
            'amount' => $request->input('amount'),
            'price' => $price,
        ]);

        return response()->json([
            'meta' => [
                'code' => 201,
                'status' => 'Success',
                'message' => 'You made new booking!'
            ],
            'data' => [
                'cars' => $carRent
            ]
        ]);
    }
}
