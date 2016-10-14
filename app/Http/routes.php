<?php

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It's a breeze. Simply tell Laravel the URIs it should respond to
| and give it the controller to call when that URI is requested.
|
*/

function rq($key = null, $default = null)
{
    if (!$key)return Request::all();
    return Request::get($key, $default);
}



function user_ins(){
    return new App\User;
}

function piano_ins(){
    return new App\Piano;
}

Route::get('/', function () {
    return view('welcome');
});

Route::any('/test' , function () {
    return 1;
});
Route::any('/user/signup' , function () {
    return user_ins()->signup();
});
Route::any('/user/login' , function () {
    return user_ins()->login();
});
Route::any('/user/logout' , function () {
    return user_ins()->logout();
});
Route::any('/user/change_password' , function () {
    return user_ins()->change_password();
});
Route::any('/user/borrow' , function () {
    return user_ins()->borrow();
});
Route::any('/user/giveback' , function () {
    return user_ins()->giveback();
});
Route::any('/user/checksession' , function () {
    return user_ins()->checksession();
});
Route::any('/user/read' , function () {
    return user_ins()->read();
});
