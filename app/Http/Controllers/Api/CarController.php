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

    public function search(Request $request){
        $validator = Validator::make($request->all(),[
            'brand' => 'nullable|string',
            'model' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed.',
                'errors' => $validator->errors()
            ], 422);
        };

        $query = Cars::query();

        if ($request->has('brand')) {
            $query->where('brand', 'like', '%' . $request->input('brand') . '%');
        }

        if ($request->has('model')) {
            $query->where('model', 'like', '%' . $request->input('model') . '%');
        }

        $cars = $query->get();
        $msg = '';
        if(count($cars) == 0){
            $msg = 'No car found!';
        }else if(count($cars) == 1){
            $msg = 'Car found!';
        }else{
            $msg = 'Cars found!';
        }

        return response()->json([
            'meta' => [
                'code' => 200,
                'status' => 'Success',
                'message' => $msg
            ],
            'data' => [
                'total' =>  count($cars),
                'cars' => $cars
            ]
        ]);
    }
}
