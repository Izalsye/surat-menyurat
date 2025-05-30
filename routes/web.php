<?php

use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', fn () => Inertia::render('Welcome', [
    'canRegister' => Route::has('register'),
]))->name('home');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/dashboard', [\App\Http\Controllers\DashboardController::class, 'index'])->name('dashboard');

    Route::get('/user', [\App\Http\Controllers\UserController::class, 'index'])->name('user.index')->middleware(\App\Enum\Permission::ViewUser->asMiddleware());
    Route::get('/user/{user}', [\App\Http\Controllers\UserController::class, 'show'])->name('user.show')->middleware(\App\Enum\Permission::ViewUser->asMiddleware());
    Route::post('/user', [\App\Http\Controllers\UserController::class, 'store'])->name('user.store')->middleware(\App\Enum\Permission::AddUser->asMiddleware());
    Route::put('/user/{user}', [\App\Http\Controllers\UserController::class, 'update'])->name('user.update')->middleware(\App\Enum\Permission::EditUser->asMiddleware());
    Route::delete('/user', [\App\Http\Controllers\UserController::class, 'massDestroy'])->name('user.mass-destroy')->middleware(\App\Enum\Permission::DeleteUser->asMiddleware());
    Route::delete('/user/{user}', [\App\Http\Controllers\UserController::class, 'destroy'])->name('user.destroy')->middleware(\App\Enum\Permission::DeleteUser->asMiddleware());

    Route::get('/role', [\App\Http\Controllers\RoleController::class, 'index'])->name('role.index')->middleware(\App\Enum\Permission::ViewRole->asMiddleware());
    Route::post('/role', [\App\Http\Controllers\RoleController::class, 'store'])->name('role.store')->middleware(\App\Enum\Permission::AddRole->asMiddleware());
    Route::put('/role/{role}', [\App\Http\Controllers\RoleController::class, 'update'])->name('role.update')->middleware(\App\Enum\Permission::EditRole->asMiddleware());
    Route::delete('/role', [\App\Http\Controllers\RoleController::class, 'massDestroy'])->name('role.mass-destroy')->middleware(\App\Enum\Permission::DeleteRole->asMiddleware());
    Route::delete('/role/{role}', [\App\Http\Controllers\RoleController::class, 'destroy'])->name('role.destroy')->middleware(\App\Enum\Permission::DeleteRole->asMiddleware());

    Route::get('/incoming-letter', [\App\Http\Controllers\IncomingLetterController::class, 'index'])->name('incoming-letter.index')->middleware(\App\Enum\Permission::ViewIncomingLetter->asMiddleware());
    Route::post('/incoming-letter/export', [\App\Http\Controllers\IncomingLetterController::class, 'export'])->name('incoming-letter.export')->middleware(\App\Enum\Permission::ViewIncomingLetter->asMiddleware());
    Route::post('/incoming-letter/read', [\App\Http\Controllers\IncomingLetterController::class, 'massMarkAsUnread'])->name('incoming-letter.mass-mark-unread')->middleware(\App\Enum\Permission::ViewIncomingLetter->asMiddleware());
    Route::post('/incoming-letter/{letter}/read', [\App\Http\Controllers\IncomingLetterController::class, 'markAsUnread'])->name('incoming-letter.mark-unread')->middleware(\App\Enum\Permission::ViewIncomingLetter->asMiddleware());
    Route::get('/incoming-letter/{letter}', [\App\Http\Controllers\IncomingLetterController::class, 'show'])->name('incoming-letter.show')->middleware(\App\Enum\Permission::ViewIncomingLetter->asMiddleware());
    Route::post('/incoming-letter', [\App\Http\Controllers\IncomingLetterController::class, 'store'])->name('incoming-letter.store')->middleware(\App\Enum\Permission::AddIncomingLetter->asMiddleware());
    Route::put('/incoming-letter/{letter}', [\App\Http\Controllers\IncomingLetterController::class, 'update'])->name('incoming-letter.update')->middleware(\App\Enum\Permission::EditIncomingLetter->asMiddleware());
    Route::delete('/incoming-letter', [\App\Http\Controllers\IncomingLetterController::class, 'massDestroy'])->name('incoming-letter.mass-destroy')->middleware(\App\Enum\Permission::DeleteIncomingLetter->asMiddleware());
    Route::delete('/incoming-letter/{letter}', [\App\Http\Controllers\IncomingLetterController::class, 'destroy'])->name('incoming-letter.destroy')->middleware(\App\Enum\Permission::DeleteIncomingLetter->asMiddleware());

    Route::get('/outgoing-letter', [\App\Http\Controllers\OutgoingLetterController::class, 'index'])->name('outgoing-letter.index')->middleware(\App\Enum\Permission::ViewOutgoingLetter->asMiddleware());
    Route::post('/outgoing-letter/export', [\App\Http\Controllers\OutgoingLetterController::class, 'export'])->name('outgoing-letter.export')->middleware(\App\Enum\Permission::ViewOutgoingLetter->asMiddleware());
    Route::get('/outgoing-letter/{letter}', [\App\Http\Controllers\OutgoingLetterController::class, 'show'])->name('outgoing-letter.show')->middleware(\App\Enum\Permission::ViewOutgoingLetter->asMiddleware());
    Route::post('/outgoing-letter', [\App\Http\Controllers\OutgoingLetterController::class, 'store'])->name('outgoing-letter.store')->middleware(\App\Enum\Permission::AddOutgoingLetter->asMiddleware());
    Route::put('/outgoing-letter/{letter}', [\App\Http\Controllers\OutgoingLetterController::class, 'update'])->name('outgoing-letter.update')->middleware(\App\Enum\Permission::EditOutgoingLetter->asMiddleware());
    Route::delete('/outgoing-letter', [\App\Http\Controllers\OutgoingLetterController::class, 'massDestroy'])->name('outgoing-letter.mass-destroy')->middleware(\App\Enum\Permission::DeleteOutgoingLetter->asMiddleware());
    Route::delete('/outgoing-letter/{letter}', [\App\Http\Controllers\OutgoingLetterController::class, 'destroy'])->name('outgoing-letter.destroy')->middleware(\App\Enum\Permission::DeleteOutgoingLetter->asMiddleware());

    Route::post('/incoming-letter/{letter}/disposition', [\App\Http\Controllers\DispositionController::class, 'store'])->name('incoming-letter.disposition.store')->middleware(\App\Enum\Permission::AddDisposition->asMiddleware());
    Route::post('/incoming-letter/{letter}/disposition/{disposition}', [\App\Http\Controllers\DispositionController::class, 'markAsDone'])->name('incoming-letter.disposition.mark-as-done');
    Route::put('/incoming-letter/{letter}/disposition/{disposition}', [\App\Http\Controllers\DispositionController::class, 'update'])->name('incoming-letter.disposition.update');
    Route::delete('/incoming-letter/{letter}/disposition/{disposition}', [\App\Http\Controllers\DispositionController::class, 'destroy'])->name('incoming-letter.disposition.destroy');

    Route::get('/letter-category', [\App\Http\Controllers\LetterCategoryController::class, 'index'])->name('letter-category.index')->middleware(\App\Enum\Permission::ViewLetterCategory->asMiddleware());
    Route::post('/letter-category', [\App\Http\Controllers\LetterCategoryController::class, 'store'])->name('letter-category.store')->middleware(\App\Enum\Permission::AddLetterCategory->asMiddleware());
    Route::put('/letter-category/{category}', [\App\Http\Controllers\LetterCategoryController::class, 'update'])->name('letter-category.update')->middleware(\App\Enum\Permission::EditLetterCategory->asMiddleware());
    Route::delete('/letter-category', [\App\Http\Controllers\LetterCategoryController::class, 'massDestroy'])->name('letter-category.mass-destroy')->middleware(\App\Enum\Permission::DeleteLetterCategory->asMiddleware());
    Route::delete('/letter-category/{category}', [\App\Http\Controllers\LetterCategoryController::class, 'destroy'])->name('letter-category.destroy')->middleware(\App\Enum\Permission::DeleteLetterCategory->asMiddleware());

    Route::name('internal-api.')->prefix('internal-api')->group(function () {
        Route::get('/dashboard/statistic', [\App\Http\Controllers\InternalAPI\DashboardController::class, 'statistic'])->name('dashboard.statistic');
        Route::get('/dashboard/process-time', [\App\Http\Controllers\InternalAPI\DashboardController::class, 'averageProcessTime'])->name('dashboard.avg-process-time');
        Route::get('/dashboard/disposition-punctuality', [\App\Http\Controllers\InternalAPI\DashboardController::class, 'dispositionPunctuality'])->name('dashboard.disposition-punctuality');
        Route::get('/dashboard/disposition-conversion', [\App\Http\Controllers\InternalAPI\DashboardController::class, 'dispositionConversionRate'])->name('dashboard.disposition-conversion');

        Route::post('/incoming-letter/extract', [\App\Http\Controllers\InternalAPI\IncomingLetterController::class, 'extract'])->name('incoming-letter.extract');
        Route::post('/incoming-letter/category', [\App\Http\Controllers\InternalAPI\IncomingLetterController::class, 'category'])->name('incoming-letter.category');
        Route::post('/incoming-letter/disposition', [\App\Http\Controllers\InternalAPI\IncomingLetterController::class, 'disposition'])->name('incoming-letter.disposition');

        Route::post('/outgoing-letter/extract', [\App\Http\Controllers\InternalAPI\OutgoingLetterController::class, 'extract'])->name('outgoing-letter.extract');
        Route::post('/outgoing-letter/category', [\App\Http\Controllers\InternalAPI\OutgoingLetterController::class, 'category'])->name('outgoing-letter.category');

        Route::get('/letter-category', [\App\Http\Controllers\InternalAPI\LetterCategoryController::class, 'index'])->name('letter-category.index');

        Route::get('/user/disposition-assignee', [\App\Http\Controllers\InternalAPI\UserController::class, 'dispositionAssignee'])->name('user.disposition-assignee');
    });
});


require __DIR__.'/settings.php';
require __DIR__.'/auth.php';
