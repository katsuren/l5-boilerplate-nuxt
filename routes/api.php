<?php

Route::post('login', 'Auth\LoginController@login');
Route::post('password/email', 'Auth\ForgotPasswordController@sendResetLinkEmail');
Route::post('password/reset', 'Auth\ResetPasswordController@reset');
Route::post('register', 'Auth\RegisterController@register');
Route::post('email/resend', 'Auth\VerificationController@resend');
Route::post('email/verify', 'Auth\VerificationController@verify')->name('api.email.verify');

Route::group(['middleware' => 'jwt.auth'], function () {
    Route::get('account', 'AccountController@index');
    Route::put('account', 'AccountController@update');
    Route::put('account/verify/{email}', 'AccountController@verify')->name('api.account.email.verify');
});
