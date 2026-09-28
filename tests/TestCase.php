<?php

declare(strict_types=1);

namespace Tests;

use App\Http\Middleware\ProtectAgainstBots;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Support\Facades\Crypt;
use Laravel\Fortify\Features;

abstract class TestCase extends BaseTestCase
{
    protected function skipUnlessFortifyFeature(string $feature, ?string $message = null): void
    {
        if (! Features::enabled($feature)) {
            $this->markTestSkipped($message ?? "Fortify feature [{$feature}] is not enabled.");
        }
    }

    /**
     * Get the bot protection fields a human would submit after filling in a form.
     *
     * @return array<string, string>
     */
    protected function botProtectionFields(int $secondsAgo = 60): array
    {
        return [
            ProtectAgainstBots::FIELD => '',
            ProtectAgainstBots::VALID_FROM_FIELD => Crypt::encryptString((string) now()->subSeconds($secondsAgo)->getTimestamp()),
        ];
    }
}
