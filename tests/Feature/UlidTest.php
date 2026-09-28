<?php

declare(strict_types=1);

use App\Models\User;
use Illuminate\Database\Schema\Builder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

uses(RefreshDatabase::class);

test('users are identified by ulids', function (): void {
    $user = User::factory()->create();

    expect(Str::isUlid($user->id))->toBeTrue()
        ->and(User::query()->find($user->id)?->is($user))->toBeTrue();
});

test('user ids are not sequential', function (): void {
    expect(Schema::getColumnType('users', 'id'))->not->toBeIn(['int8', 'bigint', 'integer'])
        ->and(Schema::getColumnType('sessions', 'user_id'))->not->toBeIn(['int8', 'bigint', 'integer']);
});

test('polymorphic relations use ulids', function (): void {
    expect(Builder::$defaultMorphKeyType)->toBe('ulid');
});
