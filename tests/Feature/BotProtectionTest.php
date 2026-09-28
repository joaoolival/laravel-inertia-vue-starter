<?php

declare(strict_types=1);

use App\Http\Middleware\ProtectAgainstBots;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Notification;
use Inertia\Testing\AssertableInertia as Assert;

uses(RefreshDatabase::class);

dataset('protected forms', [
    'registration' => ['register.store', [
        'name' => 'Test User',
        'email' => 'test@example.com',
        'password' => 'password',
        'password_confirmation' => 'password',
    ]],
    'password reset link' => ['password.email', [
        'email' => 'test@example.com',
    ]],
]);

test('pages receive the bot protection fields', function (): void {
    $this->freezeTime();

    $this->get(route('register'))->assertInertia(fn (Assert $page): Assert => $page
        ->where('botProtection.field', ProtectAgainstBots::FIELD)
        ->where('botProtection.validFromField', ProtectAgainstBots::VALID_FROM_FIELD)
        ->where('botProtection.validFrom', fn (string $validFrom): bool => (int) Crypt::decryptString($validFrom) === now()->getTimestamp()),
    );
});

test('submissions that fill the honeypot are rejected', function (string $route, array $payload): void {
    Notification::fake();

    $response = $this->post(route($route), [...$payload, ...$this->botProtectionFields(), ProtectAgainstBots::FIELD => 'https://spam.example']);

    $response->assertSessionHasErrors(ProtectAgainstBots::FIELD);
    $this->assertGuest();
    Notification::assertNothingSent();
})->with('protected forms');

test('submissions without a valid form timestamp are rejected', function (string $route, array $payload, array $botFields): void {
    Notification::fake();

    $response = $this->post(route($route), [...$payload, ...$botFields]);

    $response->assertSessionHasErrors(ProtectAgainstBots::VALID_FROM_FIELD);
    $this->assertGuest();
    Notification::assertNothingSent();
})->with('protected forms')->with([
    'missing timestamp' => [[]],
    'forged plain timestamp' => [[ProtectAgainstBots::VALID_FROM_FIELD => '1700000000']],
    'non-string timestamp' => [[ProtectAgainstBots::VALID_FROM_FIELD => ['1700000000']]],
]);

test('submissions sent faster than a human could fill the form are rejected', function (string $route, array $payload): void {
    $response = $this->post(route($route), [...$payload, ...$this->botProtectionFields(secondsAgo: ProtectAgainstBots::MINIMUM_SECONDS - 1)]);

    $response->assertSessionHasErrors([ProtectAgainstBots::VALID_FROM_FIELD => 'Please wait a moment and try again.']);
})->with('protected forms');

test('public forms are limited to five attempts per minute per ip', function (string $route): void {
    $this->freezeTime();

    foreach (range(1, 5) as $attempt) {
        $this->post(route($route))->assertSessionDoesntHaveErrors('email');
    }

    $this->post(route($route))->assertSessionHasErrors(['email' => 'Too many attempts. Please try again in 60 seconds.']);
})->with('protected forms');

test('public forms are limited to twenty attempts per hour per ip', function (string $route): void {
    foreach (range(1, 4) as $minute) {
        foreach (range(1, 5) as $attempt) {
            $this->post(route($route))->assertSessionDoesntHaveErrors('email');
        }

        $this->travel(61)->seconds();
    }

    $this->post(route($route))->assertSessionHasErrors('email');
})->with('protected forms');

test('rate limited json requests receive a 429 validation response', function (): void {
    foreach (range(1, 5) as $attempt) {
        $this->postJson(route('register.store'));
    }

    $this->postJson(route('register.store'))
        ->assertStatus(429)
        ->assertJsonValidationErrors('email');
});

test('registration attempts do not count against the password reset limit', function (): void {
    foreach (range(1, 6) as $attempt) {
        $this->post(route('register.store'));
    }

    $this->post(route('password.email'))->assertSessionDoesntHaveErrors('email');
});
