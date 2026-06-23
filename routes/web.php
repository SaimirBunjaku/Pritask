<?php

use App\Http\Controllers\CommentController;
use App\Http\Controllers\IssueController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\TagController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return auth()->check()
        ? redirect()->route('projects.index')
        : redirect()->route('login');
});

require __DIR__.'/auth.php';

Route::middleware('auth')->group(function () {
    Route::resource('projects', ProjectController::class);

    Route::resource('issues', IssueController::class);
    Route::patch('issues/{issue}/status', [IssueController::class, 'updateStatus'])->name('issues.updateStatus');
    Route::post('issues/{issue}/tags/{tag}', [IssueController::class, 'attachTag'])->name('issues.tags.attach');
    Route::delete('issues/{issue}/tags/{tag}', [IssueController::class, 'detachTag'])->name('issues.tags.detach');
    Route::post('issues/{issue}/users/{user}', [IssueController::class, 'attachUser'])->name('issues.users.attach');
    Route::delete('issues/{issue}/users/{user}', [IssueController::class, 'detachUser'])->name('issues.users.detach');
    Route::get('issues/{issue}/comments', [CommentController::class, 'index'])->name('issues.comments.index');
    Route::post('issues/{issue}/comments', [CommentController::class, 'store'])->name('issues.comments.store');
    Route::patch('issues/{issue}/comments/{comment}', [CommentController::class, 'update'])->name('issues.comments.update');
    Route::delete('issues/{issue}/comments/{comment}', [CommentController::class, 'destroy'])->name('issues.comments.destroy');

    Route::resource('tags', TagController::class)->only(['index', 'store']);

    Route::get('notifications', [NotificationController::class, 'index'])->name('notifications.index');
    Route::post('notifications/read-all', [NotificationController::class, 'markAllAsRead'])->name('notifications.readAll');
    Route::post('notifications/{id}/read', [NotificationController::class, 'markAsRead'])->name('notifications.read');
});
