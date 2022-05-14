<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class AuthApiController extends Controller
{
    public function register(Request $request) {
        $validator = Validator::make($request->all(), [
            'name' => 'required|min:3',
            'email' => 'required|min:13|unique:users',
            'password' => 'required|min:3'
        ]);

        if($validator->fails()) {
            return response()->json([
                'status' => 422,
                'message' => 'Validation error!',
                'data' => $validator->errors()
            ]);
        }

        $user = new User();
        $user->name = $request->name;
        $user->email = $request->email;
        $user->password = Hash::make($request->password);

        $user->save();

        $token = $user->createToken('laravel sanctum');

        return response()->json([
            'status' => 200,
            'message' => 'Success!',
            'data' => $token->plainTextToken
        ]);
    }


    public function login(Request $request) {
        $validator = Validator::make($request->all(),[
            'email' => 'required|min:13',
            'password' => 'required|min:3'
        ]);

        if($validator->fails()) {
            return response()->json([
                'status' => 422,
                'message' => 'Validation error!',
                'data' => $validator->errors()
            ]);
        }

        $credentails = ['email' => $request->email, 'password' => $request->password];

        if(Auth::attempt($credentails)) {
            $user = Auth::user();
            Auth::login($user);

            $token = $user->createToken('laravel sanctum', ['user:show']);

            return response()->json([
                'status' => 200,
                'message' => 'Successfully Login!',
                'token' => $token->plainTextToken
            ]);
        }else {
            return response()->json([
                'status' => 500,
                'message' => 'Login Fail!'
            ]);
        }
    }

    public function profile() {
        $user = auth()->user();
        return response()->json([
            'status' => 200,
            'message' => 'success',
            'data' => $user
        ]);
    }

    public function userLists() {

        if(!auth()->user()->tokenCan('user:list')) {
            return response()->json([
                'status' => 403,
                'message' => 'Unauthorized!'
            ]);
        }

        $users = User::all();
        return response()->json([
            'status' => 200,
            'message' => 'Success',
            'data' => $users
        ]);
    }

}
