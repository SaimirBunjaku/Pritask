<?php

use App\Http\Controllers\ProjectController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect('/projects');
});

Route::resource('projects', ProjectController::class);

Route::get('/issues', function () {
    return view('issues.index');
});

Route::get('/tags', function () {
    return view('tags.index');
});
