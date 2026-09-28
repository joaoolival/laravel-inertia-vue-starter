<?php

declare(strict_types=1);

test('web responses include security headers', function (): void {
    $response = $this->get(route('home'));

    $response->assertOk()
        ->assertHeader('X-Content-Type-Options', 'nosniff')
        ->assertHeader('X-Frame-Options', 'SAMEORIGIN')
        ->assertHeader('Referrer-Policy', 'strict-origin-when-cross-origin')
        ->assertHeader('Permissions-Policy', 'camera=(), microphone=(), geolocation=()')
        ->assertHeaderMissing('Strict-Transport-Security');
});

test('secure requests include the strict transport security header', function (): void {
    $response = $this->get('https://localhost/');

    $response->assertOk()
        ->assertHeader('Strict-Transport-Security', 'max-age=31536000');
});
