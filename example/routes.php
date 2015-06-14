<?php

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It's a breeze. Simply tell Laravel the URIs it should respond to
| and give it the Closure to execute when that URI is requested.
|
*/

Route::get('surpass_test', ['as' => 'home.surpass_test', 'uses' => 'SurpassTestController@surpass_test']);
Route::post('surpass_upload_test', ['as' => 'home.surpass_upload_test', 'uses' => 'SurpassTestController@surpass_upload_test']);
Route::post('surpass_remove_test', ['as' => 'home.surpass_remove_test', 'uses' => 'SurpassTestController@surpass_remove_test']);