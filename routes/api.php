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

Route::middleware('cors')->group(function (){
    Route::get('/categories' , 'MenuController@getCategories');
    Route::get('/items/{id?}' , 'MenuController@getItem');
    Route::get('/menus' , 'MenuController@getMenus');
    Route::get('/menu/{id}' , 'MenuController@getMenu');
    Route::get('/reviews' , 'ReviewController@getReviews');

    Route::middleware('auth:api')->group(function () {
        Route::post('/review/add' , 'ReviewController@setReview');
        Route::patch('/review/update/{id}' , 'ReviewController@updateReview');
        Route::prefix('cart')->group(function(){
            Route::get('/','CartController@getCartItems');
            Route::get('/total','CartController@getTotal');
            Route::post('/add','CartController@SetCartItem');
            Route::delete('/remove/{id}','CartController@DeleteCartItem');
            Route::delete('/decrease/{id}','CartController@DecreaseCartItem');
        });
        Route::prefix('user')->group(function(){
            Route::get('/','UserController@getUser');
            // Route::get('/points','UserController@getPoints');
            Route::get('/orders','UserController@getOrders');
            Route::post('/update','UserController@updateUser');
            Route::post('update','UserController@updateUser');
            Route::get('/addresses','UserController@getUserAddresses');
        });
        Route::prefix('address')->group(function(){
            Route::post('add' , 'UserController@addAddress');
            Route::put('/{id}/update','UserController@updateAddresse');
            Route::delete('/{id}/delete','UserController@deleteAddress');
        });
        Route::prefix('checkout')->group(function(){
            Route::post('/','CheckoutController@checkout');
        });
    });

    Route::middleware('guest:api')->group(function () {
        Route::post('/login','UserController@login')->name('api.login');
        Route::post('/register','UserController@register')->name('api.register');
    });


});
