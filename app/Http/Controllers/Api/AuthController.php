<?php

namespace App\Http\Controllers\Api;

use App\Helpers\ResponseFormatter;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Models\User;
use PHPOpenSourceSaver\JWTAuth\Facades\JWTAuth;

class AuthController extends Controller
{
    public function __construct(User $user){
        $this->user = $user;
        // $this->middleware('auth:api', ['except' => ['login', 'register']]);
    }

    public function register(Request $request){
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|min:3|max:255',
            'email' => 'required|string|email:rfc,dns|max:255|unique:users',
            'phone' => 'required|string|regex:/^[0-9]{10,14}$/|min:10|max:14|unique:users',
            'address' => 'required|string|min:10|max:255',
            'password' => [
                'required',
                'string',
                'min:6',
                'max:255',
                'regex:/^(?=.*[A-Z])(?=.*\d).+$/'
            ],
            'license_no' => 'required|string|min:2|unique:users'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation Error',
                'errors' => $validator->errors()->toArray()
            ], 400);
        }

        $user = $this->user::create([
            'name' => $request['name'],
            'email' => $request['email'],
            'phone' => $request['phone'],
            'address' => $request['address'], 
            'password'  => bcrypt($request['password']),
            'license_no'=> $request['license_no']
        ]);

        return response()->json([
            'meta' => [
                'code' => 200,
                'status' => 'Success',
                'message' => 'User created successfully!'
            ],
            'data' => [
                'user' => $user
            ]
        ]);
    }

    public function login(Request $request){
        $validator = Validator::make($request->all(), [
            'email' => 'required|string|email:rfc,dns|exists:users,email',
            'password' => 'required|string'
        ]);

        if($validator->fails()){
            return response()->json([
                'message' => "validation Error",
                'error' => $validator->errors()->toArray()
            ], 400);
        }
        
        $credentials = $request->only('email', 'password');
        if(!auth()->attempt($credentials)){
            return response()->json([
                'meta' => [
                    'code' => 401,
                    'status' => 'Unauthorized',
                    'message' => 'Invalid email or password.'
                ]
            ], 401);
        }

        $token = auth()->attempt([
            'email' => $request->email,
            'password' => $request->password
        ]);

        if($token){
            return response()->json([
                'meta' => [
                    'code' => 200,
                    'status' => 'Success',
                    'message' => 'Logged in successfully!'
                ],
                'data' => [
                    'user' => auth()->user(),
                    'access_token' => [
                        'token' => $token,
                        'type' => 'Bearer',
                        'expires_in' => auth()->factory()->getTTL() * 60
                    ]
                ]
            ]);
        }
    }

    // public function logout(){
    //     $token = JWTAuth::getToken();

    //     $invalidate = JWTAuth::invalidate($token);

    //     if($invalidate){
    //         return response()->json([
    //             'meta' => [
    //                 'code' => 200,
    //                 'status' => 'Success',
    //                 'message' => 'Successfully logged out'
    //             ],
    //             'data' => []
    //         ]);
    //     }
    // }
}
