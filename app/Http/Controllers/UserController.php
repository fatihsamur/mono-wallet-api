<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    //register new user
    public function register(Request $request)
    {
        $request->validate([
            'username' => 'required|string|max:255',
            'firstname' => 'required|string|max:255',
            'lastname' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:users',
            'password' => 'required|string|min:6',
        ]);


        $user = new User();
        $user->username = $request->username;
        $user->firstname = $request->firstname;
        $user->lastname = $request->lastname;
        $user->email = $request->email;
        $user->password = Hash::make($request->password);
        $user->save();

        $token = $user->createToken('auth-token')->plainTextToken;

        return response()->json(
            [
                'status' => 'success',
                'data' => [
                    'token' => $token,
                    'user' => $user,
                ],
            ],
            201
        );
    }

    //login user
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|string|email|max:255',
            'password' => 'required|string|min:6',
        ]);

        $user = User::where('email', $request->email)->first();

        if (!$user) {
            return response()->json(
                [
                    'status' => 'error',
                    'message' => 'User not found',
                ],
                404
            );
        }

        if (!Hash::check($request->password, $user->password)) {
            return response()->json(
                [
                    'status' => 'error',
                    'message' => 'Password is incorrect',
                ],
                401
            );
        }

        $token = $user->createToken('auth-token')->plainTextToken;

        return response()->json(
            [
                'status' => 'success',
                'data' => [
                    'token' => $token,
                    'user' => $user,
                ],
            ],
            200
        );
    }

    // get user data
    public function getUser(Request $request)
    {
        $user = $request->user();
        if ($user) {
            return response()->json(
                [
                    'status' => 'success',
                    'data' => $user->with('wallet')->get(),
                ],
                200
            );
        } else {
            return response()->json([
                'status' => 'error',
                'message' => 'User not found',
            ], 404);
        }
    }
}
