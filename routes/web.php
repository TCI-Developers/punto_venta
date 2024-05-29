<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});

Route::middleware([
    'auth:sanctum',
    config('jetstream.auth_session'),
    'verified',
])->group(function () {
    Route::get('/dashboard', function () {
        return view('dashboard');
    })->name('dashboard');
});

Route::post('/login-user', 'Admin\UserController@loginUser')->name('user.login');
Route::get('/users', 'Admin\UserController@getUserQB')->name('user.index');
// Route::get('/testt', 'Admin\UserController@test');


Route::get('/home', function(){
    return view('dashboard');
})->name('dashboard');

Route::get('/products', function(){
    return view('products');
})->name('products');

Route::get('/sales', function(){
    return view('sales');
})->name('sales');


Route::get('/billing', function(){
    return view('billing');
})->name('billing');

Route::get('/tickets', function(){
    return view('tickets');
})->name('tickets');

Route::get('/stock', function(){
    return view('stock');
})->name('stock');

Route::get('/order', function(){
    return view('order-form');
})->name('order');

Route::post('/product', 'Admin\UserController@preparePost')->name('productPost');