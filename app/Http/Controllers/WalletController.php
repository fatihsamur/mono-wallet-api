<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;

class WalletController extends Controller
{

    // create new wallet
    public function createWallet(Request $request)
    {
        $request->validate([
            'user_id' => 'required|integer',
            'balance' => 'required|integer',
        ]);

        $user = User::find($request->user_id);

        if (!$user) {
            return response()->json(
                [
                    'status' => 'error',
                    'message' => 'User not found',
                ],
                404
            );
        }

        $user->wallet()->create([
            'balance' => $request->balance,
        ]);
        return response()->json(
            [
                'status' => 'success',
                'data' => [
                    'user' => $user->with('wallet')->first(),
                ],
            ],
            201
        );
    }

    // assignBalance
    public function assignBalance(Request $request)
    {
        $request->validate([
            'user_id' => 'required|integer',
            'balance' => 'required|integer',
        ]);

        $user = User::find($request->user_id);
        $oldBalance = $user->wallet->balance;
        $user->wallet->balance = $request->balance;
        $user->wallet->save();
        return response()->json(
            [
                'status' => 'success',
                'data' => [
                    'user' => $user->with('wallet')->first(),
                    'old_balance' => $oldBalance,
                    'new_balance' => $user->wallet->balance,
                ],
            ],
            200
        );
    }
}
