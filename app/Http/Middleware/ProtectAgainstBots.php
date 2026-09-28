<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Contracts\Encryption\DecryptException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpFoundation\Response;

/**
 * Rejects form submissions that fill the hidden honeypot field or that arrive
 * faster than a human could fill the form in.
 */
final class ProtectAgainstBots
{
    public const string FIELD = 'website';

    public const string VALID_FROM_FIELD = 'valid_from';

    public const int MINIMUM_SECONDS = 2;

    /**
     * Get the hidden fields that protected forms must submit.
     *
     * @return array{field: string, validFromField: string, validFrom: string}
     */
    public static function fields(): array
    {
        return [
            'field' => self::FIELD,
            'validFromField' => self::VALID_FROM_FIELD,
            'validFrom' => Crypt::encryptString((string) now()->getTimestamp()),
        ];
    }

    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     *
     * @throws ValidationException
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (filled($request->input(self::FIELD))) {
            throw ValidationException::withMessages([
                self::FIELD => __('Your submission could not be processed.'),
            ]);
        }

        if (! $this->wasFilledInByAHuman($request->input(self::VALID_FROM_FIELD))) {
            throw ValidationException::withMessages([
                self::VALID_FROM_FIELD => __('Please wait a moment and try again.'),
            ]);
        }

        return $next($request);
    }

    /**
     * Determine if the encrypted form timestamp is valid and old enough.
     */
    private function wasFilledInByAHuman(mixed $validFrom): bool
    {
        if (! is_string($validFrom)) {
            return false;
        }

        try {
            $timestamp = (int) Crypt::decryptString($validFrom);
        } catch (DecryptException) {
            return false;
        }

        return now()->getTimestamp() - $timestamp >= self::MINIMUM_SECONDS;
    }
}
