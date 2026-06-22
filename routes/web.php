<?php

use App\Http\Controllers\IssueController;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\TagController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect('/projects');
});

Route::resource('projects', ProjectController::class);

Route::resource('issues', IssueController::class);
Route::patch('issues/{issue}/status', [IssueController::class, 'updateStatus'])->name('issues.updateStatus');
Route::post('issues/{issue}/tags/{tag}', [IssueController::class, 'attachTag'])->name('issues.tags.attach');
Route::delete('issues/{issue}/tags/{tag}', [IssueController::class, 'detachTag'])->name('issues.tags.detach');

Route::resource('tags', TagController::class)->only(['index', 'store']);
