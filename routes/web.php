<?php

use App\Http\Controllers\IssueController;
use App\Http\Controllers\ProjectController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect('/projects');
});

Route::resource('projects', ProjectController::class);

Route::resource('issues', IssueController::class);
Route::patch('issues/{issue}/status', [IssueController::class, 'updateStatus'])->name('issues.updateStatus');

Route::get('/tags', function () {
    return view('tags.index');
});
