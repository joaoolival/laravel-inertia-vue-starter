<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rules\Password;

function passwordPasses(string $password): bool
{
    return Validator::make(['password' => $password], ['password' => Password::default()])->passes();
}

function fakePwnedPasswordsApi(string $compromisedPassword = ''): void
{
    $hash = mb_strtoupper(sha1($compromisedPassword));

    Http::fake([
        'api.pwnedpasswords.com/*' => Http::response($compromisedPassword === '' ? '' : mb_substr($hash, 5).':42'),
    ]);
}

test('simple passwords are allowed outside production', function (): void {
    expect(passwordPasses('password'))->toBeTrue();
});

test('weak passwords are rejected in production', function (string $password): void {
    app()->instance('env', 'production');

    expect(passwordPasses($password))->toBeFalse();
})->with([
    'too short' => ['Sh0rt!pass'],
    'no uppercase' => ['lowercase-only-1!'],
    'no lowercase' => ['UPPERCASE-ONLY-1!'],
    'no numbers' => ['No-Numbers-Here!'],
    'no symbols' => ['NoSymbolsHere123'],
]);

test('strong uncompromised passwords are accepted in production', function (): void {
    app()->instance('env', 'production');
    fakePwnedPasswordsApi();

    expect(passwordPasses('Correct-Horse-Battery-9'))->toBeTrue();
});

test('compromised passwords are rejected in production', function (): void {
    app()->instance('env', 'production');
    fakePwnedPasswordsApi('Correct-Horse-Battery-9');

    expect(passwordPasses('Correct-Horse-Battery-9'))->toBeFalse();
});
