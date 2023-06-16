<?php

use App\Http\Controllers\UserPortraitController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});

Route::get('/setUserPortrait', [UserPortraitController::class, 'setUserPortrait']);
Route::post('/setUserPortrait', [UserPortraitController::class, 'setUserPortrait']);

Route::get('/getUserScoreInfo', [UserPortraitController::class, 'getUserScoreInfo']);
Route::post('/getUserScoreInfo', [UserPortraitController::class, 'getUserScoreInfo']);

Route::get('/getUserScoreInfoByUserId', [UserPortraitController::class, 'getUserScoreInfoByUserId']);
Route::post('/getUserScoreInfoByUserId', [UserPortraitController::class, 'getUserScoreInfoByUserId']);
