<?php

declare(strict_types=1);

use App\Http\Controllers\Controller;
use Illuminate\Database\Eloquent\Concerns\HasUlids;

arch()->preset()->php();

arch()->preset()->security();

arch()->preset()->laravel();

arch('code uses strict types')
    ->expect('App')
    ->toUseStrictTypes();

arch('code uses strict equality')
    ->expect('App')
    ->toUseStrictEquality();

arch('classes are final')
    ->expect('App')
    ->classes()
    ->toBeFinal()
    ->ignoring(Controller::class);

arch('actions are final readonly classes')
    ->expect('App\Actions')
    ->classes()
    ->toBeFinal()
    ->toBeReadonly();

arch('actions have a handle method')
    ->expect('App\Actions')
    ->classes()
    ->toHaveMethod('handle')
    ->ignoring('App\Actions\Fortify');

arch('actions only expose the handle method')
    ->expect('App\Actions')
    ->classes()
    ->not->toHavePublicMethodsBesides(['__construct', 'handle'])
    ->ignoring('App\Actions\Fortify');

arch('actions have no private helper methods')
    ->expect('App\Actions')
    ->classes()
    ->not->toHavePrivateMethods()
    ->ignoring('App\Actions\Fortify');

arch('actions have no protected helper methods')
    ->expect('App\Actions')
    ->classes()
    ->not->toHaveProtectedMethods()
    ->ignoring('App\Actions\Fortify');

arch('controllers do not query the database directly')
    ->expect('App\Http\Controllers')
    ->not->toUse(Illuminate\Support\Facades\DB::class);

arch('env is only read in config files')
    ->expect('env')
    ->not->toBeUsed();

arch('models use ulids instead of sequential ids')
    ->expect('App\Models')
    ->classes()
    ->toUseTrait(HasUlids::class);
