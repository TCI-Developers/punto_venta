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

Route::middleware([
    'auth:sanctum',
    config('jetstream.auth_session'),
    'verified',
])->group(function () {
    // Route::get('/dashboard', function () {
    //     return view('dashboard');
    // })->name('dashboard');
});

Route::post('/login-user', 'Admin\UserController@loginUser')->name('user.login');
Route::get('/users', 'Admin\UserController@getUserQB')->name('user.getUserQB');
Route::get('/get-products', 'Admin\UserController@getProducts')->name('user.getProducts');
Route::get('/get-brands', 'Admin\UserController@getBrands')->name('user.getBrands');
Route::get('/get-customers', 'Admin\UserController@getCustomers')->name('user.getCustomers');
Route::get('/get-payment-methods', 'Admin\UserController@getPaymentMethods')->name('user.getPaymentMethods');
Route::get('/get-unidades-sat', 'Admin\UserController@getUnidadesSat')->name('user.getUnidadesSat');

// Route::get('/login', function(){
//     return view('auth.login');
// })->name('login');