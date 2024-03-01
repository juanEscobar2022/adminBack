<?php

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

// Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
//     return $request->user();
// });



Route::group(['middleware' => ['cors', 'authentication']], function () {


   //Route::apiResource('/usuario','App\Http\Controllers\Users\UserContoller');



   Route::post('/menu', 'App\Http\Controllers\Menu\MenuController@getMenu');
});


Route::apiResource('/usuario', 'App\Http\Controllers\Users\UserContoller');


Route::post('/login', 'App\Http\Controllers\Login\LoginController@authentication');
Route::post('/logout', 'App\Http\Controllers\Login\LoginController@closesesion');
Route::post('/checktoken', 'App\Http\Controllers\Login\LoginController@validatetokenss');

Route::apiResource('/rol', 'App\Http\Controllers\Role\RoleController');
Route::apiResource('/category', 'App\Http\Controllers\Category\CategoryController');




