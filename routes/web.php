<?php

Auth::routes([
    'register' => false,
    'reset' => false,
    'verify' => false
]);

Route::group(['middleware' => 'auth'], function () {
    Route::get('/', function () {
        return view('home');
    });

    Route::get('/home', 'HomeController@index')->name('home');

    Route::group(['middleware' => 'role:admin'], function () {
        Route::get('/users', 'UsersController@index')->name('users');
        Route::get('/users/invite', 'UsersController@invite')->name('invite');
        Route::post('/users/invite', 'UsersController@processInvite')->name('process_invite');
    });
});

Route::get('/registration/{token}', 'UsersController@registration')->name('registration');
Route::post('/registration', 'Auth\RegisterController@register')->name('accept');
