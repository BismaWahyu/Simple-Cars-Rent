<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;

class UserController extends Controller
{
    public function __construct(User $user){
        $this->user = $user;
    }

    public function me(){
        if (!auth()->check()) {
            return response()->json([
                'meta' => [
                    'code' => 401,
                    'status' => 'Unauthorized',
                    'message' => 'You are not authorized.'
                ]
            ], 401);
        }

        return response()->json([
            'meta' => [
                'code' => 200,
                'status' => 'Success',
                'message' => 'Data user fetched!'
            ],
            'data' => [
                'user' => auth()->user()
            ]
        ]);
    }
}
