<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;
use App\Models\Cars;
use App\Models\User;
use App\Models\CarsRent;
use App\Models\CarsReturn;
use Carbon\Carbon;

class RentController extends Controller
{
    public function __construct(CarsRent $rent){
        $this->rent = $rent;
    }

    public function index(){
        $rents = CarsRent::with('car', 'user')->get();
        foreach ($rents as $rent) {
            unset($rent->user_id);
            unset($rent->car_id);
        }

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
        $validator = Validator::make(['id' => $id], [
            'id' => 'required|integer'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed.',
                'errors' => $validator->errors()
            ], 422);
        }

        $rent_detail = CarsRent::with('car', 'user')->find($id);
        unset($rent_detail->user_id);
        unset($rent_detail->car_id);

        if (!$rent_detail) {
            return response()->json([
                'meta' => [
                    'code' => 200,
                    'status' => 'Success',
                    'message' => 'Book detail not found!'
                ],
                'data' => null
            ], 404);
        }

        return response()->json([
            'meta' => [
                'code' => 200,
                'status' => 'Success',
                'message' => 'Book detail loaded!'
            ],
            'data' => [
                'detail' => $rent_detail
            ]
        ]);
    }

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

        if($car->stock == 0){
            return response()->json([
                'meta' => [
                    'code' => 400,
                    'status' => 'Bad Request',
                    'message' => 'Selected car is out of stock'
                ],
                'data' => [
                    'cars' => []
                ]
            ]);
        }

        if($car->available == 0){
            return response()->json([
                'meta' => [
                    'code' => 400,
                    'status' => 'Bad Request',
                    'message' => 'No car  available for rent.'
                ],
                'data' => [
                    'cars' => []
                ]
            ]);
        }

        try {
            $this->checkRentalAvailability($car->id, $request->input('start_date'), $request->input('end_date'));

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

            $car->available = $car->available - $request->input('amount');
            $car->save();

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
        } catch (\Exception $e) {
            return response()->json([
                'meta' => [
                    'code' => 422,
                    'status' => 'Success',
                    'message' => $e->getMessage()
                ],
                'data' => [
                    'cars' => []
                ]
            ], 422);
        }
    }

    public function return(Request $request){
        $validator = Validator::make($request->all(),[
            "id" => 'required|integer|exists:cars_rent,id',
            "actual_return_date" => 'required|after:start_date'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed.',
                'errors' => $validator->errors()
            ], 422);
        }

        try{    
            $penalty = $this->checkPenaltyFee($request->id, $request->return_date);
            $rental = CarsRent::with('car')->find($request->id);

            $returnCar = CarsReturn::create([
                'user_id' => $rental->user->id,
                'car_id' => $rental->car->id,
                'return_date' => $rental->end_date,
                'actual_return_date' => $request->input('actual_return_date'),
                'penalty_fee' => $penalty
            ]);

            return response()->json([
                'meta' => [
                    'code' => 201,
                    'status' => 'Success',
                    'message' => 'Thank you!'
                ],
                'data' => [
                    'cars' => $returnCar
                ]
            ]);
            
        }catch(\Exception $e){
            return response()->json([
                'meta' => [
                    'code' => 422,
                    'status' => 'Success',
                    'message' => $e->getMessage()
                ],
                'data' => [
                    'cars' => []
                ]
            ], 422);
        }

    }

    /**
     * Utils
     */
    public function checkRentalAvailability($carId, $endDate){
        $existingRentals = CarsRent::where('car_id', $carId)
            ->where(function ($query) use ($startDate, $endDate) {
                $query->where(function ($q) use ($startDate, $endDate) {
                    $q->where('start_date', '<', $endDate)
                        ->where('end_date', '>', $startDate);
                })->orWhere(function ($q) use ($startDate, $endDate) {
                    $q->where('start_date', '>=', $startDate)
                        ->where('end_date', '<=', $endDate);
                });
            })
            ->exists();
            
            $car = Cars::find($carId); // Check car availability

            if ($existingRentals || $car->available <= 0) {
                throw new \Exception('Car is either already rented during the selected dates or currently out of stock.');
            }
    }

    public function checkPenaltyFee($rentId, $returnDate){
        $rental = CarsRent::with('car')->find($rentId);
        
        $penaltyRate = $rental->car->penalty_rate;
        
        $endDate = Carbon::parse($rental->end_date);
        $returnDate = Carbon::parse($returnDate);

        $timeDifferenceInHours = intval($returnDate->diffInHours($endDate, true));
        if ($timeDifferenceInHours <= 0) {
            return 0;
        }

        $penaltyFee = $timeDifferenceInHours * $penaltyRate;
        return $penaltyFee;
    }
}
