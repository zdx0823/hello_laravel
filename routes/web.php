<?php

use Illuminate\Support\Facades\Route;


Route::get('/', 'StaticPagesController@home')->name('home');
Route::get('/help', 'StaticPagesController@help')->name('help');
Route::get('/about', 'StaticPagesController@about')->name('about');

Route::get('/sigup', 'UsersController@create')->name('sigup');

Route::resource('users', 'UsersController');