<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\VocabulariesController;
use App\Http\Controllers\VocabularySetController;
use App\Http\Controllers\DefaultTopicsController;
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

    //
    Route::post('vocabularies', [VocabulariesController::class,'index']);
    Route::post('create-vocabularies', [VocabulariesController::class,'create']);
    Route::post('update-vocabularies', [VocabulariesController::class,'update']);
    Route::post('delete-vocabularies', [VocabulariesController::class,'destroy']);
    //
    Route::get('vocabulary_sets', [VocabularySetController::class,'index']);
    Route::post('update-vocabulary_sets', [VocabularySetController::class,'update']);
    Route::post('create-vocabulary_sets', [VocabularySetController::class,'store']);
    Route::post('delete-vocabulary_sets', [VocabularySetController::class,'destroy']);
    //
    Route::get('default_topics_id', [DefaultTopicsController::class,'index']);
    Route::post('update-default_topics_id', [DefaultTopicsController::class,'update']);
    Route::post('create-default_topics_id', [DefaultTopicsController::class,'store']);
    Route::post('delete-default_topics_id', [DefaultTopicsController::class,'destroy']);
});

