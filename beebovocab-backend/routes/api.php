<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\VocabulariesController;
use App\Http\Controllers\VocabularySetController;
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

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::group(['prefix' => 'auth'], function ($router) {
    Route::post('login', [AuthController::class, 'store'])->name('login');
    Route::post('logout', [AuthController::class, 'destroy']);
    Route::post('refresh', [AuthController::class, 'refresh']);
    Route::get('user', [AuthController::class, 'index']);
});

Route::group(['prefix' => 'vocabs'], function ($router) {
    Route::post('create', [VocabulariesController::class, 'create']);
});

Route::group(['prefix' => 'vocabulary_sets'], function ($router) {
    Route::get('/', [VocabularySetController::class,'index']);
    Route::get('/default', [VocabularySetController::class,'showDefaultVocab']);
    Route::post('update', [VocabularySetController::class,'update']);
    Route::post('create', [VocabularySetController::class,'store']);
    Route::post('delete', [VocabularySetController::class,'destroy']);
});

