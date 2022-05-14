<?php

namespace App\Http\Controllers;

use App\Models\Promotion;
use App\Models\User;
use Illuminate\Http\Request;

class PromotionController extends Controller
{
    // create new promotion
    public function store(Request $request)
    {
        $request->validate([
            'start_date' => 'required|date',
            'end_date' => 'required|date',
            'amount' => 'required|int',
            'quota' => 'required|int',
        ]);

        $promotion = new Promotion();
        $promotion->code = $this->generateCode();
        $promotion->start_date = $request->start_date;
        $promotion->end_date = $request->end_date;
        $promotion->amount = $request->amount;
        $promotion->quota = $request->quota;
        $promotion->save();

        return response()->json(
            [
                'status' => 'success',
                'data' => [
                    'id' => $promotion->id,
                    'code' => $promotion->code,
                    'start_date' => $promotion->start_date,
                    'end_date' => $promotion->end_date,
                    'amount' => $promotion->amount,
                    'quota' => $promotion->quota,

                ],
            ],
            201

        );
    }

    // will replace query with better solution?
    // generate unique 12 char code
    private function generateCode()
    {
        $code = '';
        $chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
        $charsLength = strlen($chars);
        for ($i = 0; $i < 12; $i++) {
            $code .= $chars[rand(0, $charsLength - 1)];
        }
        // check if the code exists
        if (Promotion::where('code', $code)->exists()) {
            return $this->generateCode();
        }

        return $code;
    }

    // get all promotions with users
    public function getPromotionCodes()
    {
        $promotions = Promotion::with('users', 'users.wallet')->get();

        // check if there are any promotions
        if (!$promotions->count()) {
            return response()->json(
                [
                    'success' => 'false',
                    'message' => 'No promotions found',
                    'data' => [],
                ],
                404
            );
        }

        $data = [];
        foreach ($promotions as $promotion) {
            $users = [];
            foreach ($promotion->users as $user) {
                $users[] = [
                    'id' => $user->id,
                    'username' => $user->username,
                    'firstname' => $user->firstname,
                    'lastname' => $user->lastname,
                    'email' => $user->email,
                    'wallet' => [
                        'id' => $user->wallet->id,
                        'balance' => $user->wallet->balance,
                        'updated_at' => $user->wallet->updated_at,
                    ],
                ];
            }
            $data[] = [
                'id' => $promotion->id,
                'code' => $promotion->code,
                'start_date' => $promotion->start_date,
                'end_date' => $promotion->end_date,
                'amount' => $promotion->amount,
                'quota' => $promotion->quota,
                'users' => $users,
            ];
        }

        return response()->json([
            'success' => true,
            'data' => $data,
        ]);
    }

    // get promotion by id
    public function getPromotionCodeById($id)
    {
        $promotion = Promotion::with('users', 'users.wallet')->find($id);

        // check if there is a promotion with this id
        if (!$promotion) {
            return response()->json(
                [
                    'success' => 'false',
                    'message' => 'No promotion found',
                    'data' => [],
                ],
                404
            );
        }

        $users  = [];
        foreach ($promotion->users as $user) {
            $users[] = [
                'id' => $user->id,
                'username' => $user->username,
                'firstname' => $user->firstname,
                'lastname' => $user->lastname,
                'email' => $user->email,
                'wallet' => [
                    'id' => $user->wallet->id,
                    'balance' => $user->wallet->balance,
                    'updated_at' => $user->wallet->updated_at,
                ],
            ];
        }


        $data = [
            'id' => $promotion->id,
            'code' => $promotion->code,
            'start_date' => $promotion->start_date,
            'end_date' => $promotion->end_date,
            'amount' => $promotion->amount,
            'quota' => $promotion->quota,
            'users' => $users,
        ];

        return response()->json([
            'success' => true,
            'data' => $data,
        ]);
    }

    // assign promotion to user
    public function assignPromotion(Request $request)
    {
        $user = $request->user();

        $request->validate([
            'code' => 'required|string|max:255',
        ]);

        // check if promotion exists
        $promotion = Promotion::where('code', $request->code)->first();
        if (!$promotion) {
            return response()->json([
                'success' => false,
                'message' => 'Promotion not found',
            ]);
        }

        // check if promotion is not expired
        if ($promotion->end_date < now()) {
            return response()->json([
                'success' => false,
                'message' => 'Promotion is expired',
            ]);
        }

        // check if the quota is not reached
        $count = $user->promotions()->where('promotion_id', $promotion->id)->count();
        if ($count >= $promotion->quota) {
            return response()->json([
                'success' => false,
                'message' => 'Promotion quota reached',
            ]);
        }

        // check if user already has promotion
        if ($user->promotions()->where('promotion_id', $promotion->id)->exists()) {
            return response()->json([
                'success' => false,
                'message' => 'User already has this promotion',
            ]);
        }

        // check if user has wallet
        if (!$user->wallet) {
            return response()->json([
                'success' => false,
                'message' => 'User has no wallet, please create one to assign promotion',
            ]);
        }

        // assign promotion balance to user's wallet
        $user->wallet()->increment('balance', $promotion->amount);


        // assign promotion to user
        $user->promotions()->attach($promotion->id);

        return response()->json([
            'success' => true,
        ]);
    }
}
