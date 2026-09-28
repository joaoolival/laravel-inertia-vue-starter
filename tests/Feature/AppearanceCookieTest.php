<?php

declare(strict_types=1);

test('a valid appearance cookie is applied to the page', function (): void {
    $response = $this->withUnencryptedCookie('appearance', 'dark')->get(route('home'));

    $response->assertOk()
        ->assertSee('class="dark"', escape: false)
        ->assertSee("const appearance = 'dark';", escape: false);
});

test('an invalid appearance cookie falls back to system', function (string $appearance): void {
    $response = $this->withUnencryptedCookie('appearance', $appearance)->get(route('home'));

    $response->assertOk()
        ->assertSee("const appearance = 'system';", escape: false)
        ->assertDontSee($appearance, escape: false);
})->with([
    'script injection' => ["';alert(1);//"],
    'closing tag' => ['</script><script>alert(1)</script>'],
    'unknown value' => ['sepia'],
]);
