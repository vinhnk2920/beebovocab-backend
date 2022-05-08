<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\DefaultTopicsController;
use App\Http\Controllers\FriendController;
use App\Http\Controllers\RelatedQuestionsController;
use App\Http\Controllers\ReviewController;
use App\Http\Controllers\UserController;
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
    Route::get('/{set_id}', [VocabulariesController::class, 'index']);
    Route::post('/create', [VocabulariesController::class, 'create']);
    Route::post('/update', [VocabulariesController::class, 'update']);
    Route::post('/delete', [VocabulariesController::class, 'delete']);
    Route::post('/review', [VocabulariesController::class, 'review']);
});

Route::group(['prefix' => 'topics'], function ($router) {
    Route::get('/', [DefaultTopicsController::class, 'index']);
    Route::get('/{id}', [DefaultTopicsController::class, 'showTopicById']);
    Route::post('/create', [DefaultTopicsController::class, 'store']);
    Route::post('/update', [DefaultTopicsController::class, 'update']);
    Route::post('/delete', [DefaultTopicsController::class, 'delete']);
});

Route::group(['prefix' => 'related_questions'], function ($router) {
    Route::get('/', [RelatedQuestionsController::class, 'index']);
    Route::post('/create', [RelatedQuestionsController::class, 'store']);
});

Route::group(['prefix' => 'vocabulary_sets'], function ($router) {
    Route::get('/', [VocabularySetController::class,'index']);
    Route::get('/default', [VocabularySetController::class,'showDefaultSet']);
    Route::get('/findByTopicId/{topic_id}', [VocabularySetController::class,'findByTopicId']);
    Route::get('/{id}', [VocabularySetController::class,'showSet']);
    Route::post('update', [VocabularySetController::class,'update']);
    Route::post('create', [VocabularySetController::class,'store']);
    Route::post('delete', [VocabularySetController::class,'destroy']);
});

Route::group(['prefix' => 'review'], function ($router) {
    Route::get('/count-level', [ReviewController::class,'countLevel']);
    Route::get('/vocabs', [ReviewController::class,'showVocab']);
    Route::get('/lately-review-sets', [ReviewController::class,'latelyReviewSets']);
});

Route::group(['prefix' => 'friend'], function ($router) {
    Route::post('/findByPhoneOrEmail', [FriendController::class,'findByPhoneOrEmail']);
    Route::post('/addFriendRequest', [FriendController::class,'addFriendRequest']);
    Route::post('/deleteFriendRequest', [FriendController::class,'deleteFriendRequest']);
    Route::post('/updateFriendStatus', [FriendController::class,'updateFriendStatus']);
    Route::post('/', [FriendController::class,'show']);
});

Route::group(['prefix' => 'users'], function ($router) {
    Route::post('/', [UserController::class,'index']);
    Route::post('/findByEmailOrPhone', [UserController::class,'findByEmailOrPhone']);
    Route::post('/delete', [UserController::class,'delete']);
});
