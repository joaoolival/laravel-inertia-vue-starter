<?php

declare(strict_types=1);

use App\Http\Middleware\ProtectAgainstBots;
use Illuminate\Support\Facades\Route;
use Laravel\Fortify\Features;
use Laravel\Fortify\Http\Controllers\PasswordResetLinkController;
use Laravel\Fortify\Http\Controllers\RegisteredUserController;
use Laravel\Fortify\RoutePath;

/*
 * Fortify registers these routes without a rate limiter. Redefining them here
 * (same URI and name, loaded after Fortify) replaces them with protected ones.
 */
Route::group([
    'domain' => config('fortify.domain'),
    'prefix' => config('fortify.prefix'),
    'middleware' => ['guest:'.config()->string('fortify.guard')],
], function (): void {
    if (Features::enabled(Features::registration())) {
        Route::post(RoutePath::for('register', '/register'), [RegisteredUserController::class, 'store'])
            ->middleware(['throttle:register', ProtectAgainstBots::class])
            ->name('register.store');
    }

    if (Features::enabled(Features::resetPasswords())) {
        Route::post(RoutePath::for('password.email', '/forgot-password'), [PasswordResetLinkController::class, 'store'])
            ->middleware(['throttle:password-reset-link', ProtectAgainstBots::class])
            ->name('password.email');
    }
});
