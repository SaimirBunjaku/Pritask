<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect('/projects');
});

Route::get('/projects', function () {
    return view('projects.index');
});

Route::get('/issues', function () {
    return view('issues.index');
});

Route::get('/tags', function () {
    return view('tags.index');
});
