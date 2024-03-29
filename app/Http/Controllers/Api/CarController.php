<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Models\Cars;

class CarController extends Controller
{
    public function __construct(Cars $car){
        $this->car = $car;
    }

    public function index(){
        $cars = Cars::all();

        return response()->json([
            'meta' => [
                'code' => 200,
                'status' => 'Success',
                'message' => 'All cars fetched!'
            ],
            'data' => [
                'total' =>  count($cars),
                'cars' => $cars
            ]
        ]);
    }

    public function detail(Request $request, $id){
        $validator = Validator::make(['id'=> $id], [
            'id' => 'required|integer'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed.',
                'errors' => $validator->errors()
            ], 422);
        }

        $car = Cars::find($id);

        if (!$car) {
            return response()->json([
                'meta' => [
                    'code' => 200,
                    'status' => 'Success',
                    'message' => 'Car not found!'
                ],
                'data' => null
            ], 404);
        }

        return response()->json([
            'meta' => [
                'code' => 200,
                'status' => 'Success',
                'message' => 'Car detail found!'
            ],
            'data' => [
                'cars' => $car
            ]
        ]);
    }
}
