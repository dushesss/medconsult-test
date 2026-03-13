<?php

declare(strict_types=1);

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/email/verify/{id}/{hash}', function (Request $request, string $id, string $hash) {
    $user = User::query()->findOrFail($id);

    if (! hash_equals((string) $hash, sha1($user->getEmailForVerification()))) {
        abort(403, 'Неверная ссылка');
    }

    if (! $user->hasVerifiedEmail()) {
        $user->markEmailAsVerified();
        event(new Illuminate\Auth\Events\Verified($user));
    }

    return response('Email подтверждён. Можно войти в приложение.', 200)
        ->header('Content-Type', 'text/plain; charset=UTF-8');
})->middleware(['signed', 'throttle:6,1'])->name('verification.verify');
