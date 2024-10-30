<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/game', function () {
    return view('game');
});

Route::get('/lobby', function () {
    return view('lobby');
});

Route::get('/rpg', function () {
    return view('rpg-game');
});
