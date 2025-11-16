<?php

declare(strict_types=1);

use App\Models\User;

it('can store a language', function (): void {
    $user = User::factory()->create();
    $response = $this->actingAs($user)->post(route('locale.store'), ['language' => 'en']);
    $response->assertRedirect();
});

it('stores locale in session and redirects back', function (): void {
    $locale = 'fr';
    $user = User::factory()->create();
    session()->forget('locale');
    $response = $this->actingAs($user)->post(route('locale.store'), [
        'locale' => $locale,
    ]);

    $response->assertRedirect();

    expect(session('locale'))->toBe($locale);
});

it('validates locale before storing', function (): void {
    session()->forget('locale');
    $user = User::factory()->create();
    $response = $this->actingAs($user)->post(route('locale.store'), [
        'locale' => '',
    ]);

    $response->assertSessionHasErrors('locale');

    expect(session()->has('locale'))->toBeFalse();
});

it('handles invalid locale data', function (): void {
    $user = User::factory()->create();
    session()->forget('locale');
    $response = $this->actingAs($user)->post(route('locale.store'), [
        'locale' => 'invalid_locale_code_that_should_fail',
    ]);

    $response->assertSessionHasErrors('locale');
});

it('overwrites existing locale in session', function (): void {
    $user = User::factory()->create();
    session(['locale' => 'en']);

    $newLocale = 'ar';
    $response = $this->actingAs($user)->post(route('locale.store'), [
        'locale' => $newLocale,
    ]);

    $response->assertRedirect();

    expect(session('locale'))->toBe($newLocale);
});

it('redirects to previous page', function (): void {
    $user = User::factory()->create();
    $previousUrl = url('/previous-page');

    $response = $this->actingAs($user)->from($previousUrl)->post(route('locale.store'), [
        'locale' => 'es',
    ]);

    $response->assertRedirect($previousUrl);
});
