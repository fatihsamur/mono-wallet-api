<?php

use App\Http\Controllers\PromotionController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\WalletController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/




/* 
User Authentication Routes
*/
// Laravel Sanctum Middleware
Route::middleware('auth:sanctum')->get('user', function (Request $request) {
    return $request->user();
});

// POST  api/auth-service/register
Route::post('/auth-service/register', [UserController::class, 'register']);

// POST  api/auth-service/login
Route::post('/auth-service/login', [UserController::class, 'login']);

// GET   api/auth-service/get-user
Route::get('/auth-service/get-user', [UserController::class, 'getUser'])->middleware('auth:sanctum');


/*
Other Backoffice Routes
*/
// POST api/backoffice/create-wallet
Route::post('/backoffice/create-wallet', [WalletController::class, 'createWallet']);

// POST api/backoffice/assing-balance
Route::post('/backoffice/assign-balance', [WalletController::class, 'assignBalance']);



/*
Mono Wallet Service Routes
*/
// POST  api/backoffice/promotion-codes
Route::post('/backoffice/promotion-codes', [PromotionController::class, 'store']);

// GET  api/backoffice/promotion-codes
Route::get('/backoffice/promotion-codes', [PromotionController::class, 'getPromotionCodes']);

// GET  api/backoffice/promotion-codes/{id}
Route::get('/backoffice/promotion-codes/{id}', [PromotionController::class, 'getPromotionCodeById']);

// POST api/assign-promotion
Route::post('/assign-promotion', [PromotionController::class, 'assignPromotion'])->middleware('auth:sanctum');
