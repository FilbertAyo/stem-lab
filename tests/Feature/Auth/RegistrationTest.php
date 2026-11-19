<?php

use App\Models\User;
use Database\Seeders\SubscriptionPackageSeeder;
use Illuminate\Support\Facades\Mail;

test('registration screen can be rendered', function () {
    $response = $this->get('/register');

    $response->assertStatus(200);
});

test('new users can register', function () {
    $response = $this->post('/register', [
        'name' => 'Test User',
        'email' => 'test@example.com',
        'phone' => '0765890921',
        'role' => 'teacher',
        'password' => 'Password1',
        'password_confirmation' => 'Password1',
    ]);

    $this->assertAuthenticated();
    $response->assertRedirect(route('dashboard', absolute: false));
});

test('registration starts a seven day trial when payments are enabled', function () {
    Mail::fake();
    config(['subscriptions.payments_enabled' => true]);
    $this->seed(SubscriptionPackageSeeder::class);

    $this->post('/register', [
        'name' => 'Trial User',
        'email' => 'trial-user@example.com',
        'phone' => '0765890921',
        'role' => 'teacher',
        'password' => 'Password1',
        'password_confirmation' => 'Password1',
    ])->assertRedirect(route('dashboard', absolute: false));

    $user = User::query()->where('email', 'trial-user@example.com')->first();

    expect($user)->not->toBeNull()
        ->and($user->subscription_tier)->toBe('trial')
        ->and($user->trial_used_at)->not->toBeNull()
        ->and($user->subscription_expires_at)->not->toBeNull()
        ->and($user->subscription_expires_at->isAfter(now()->addDays(6)))->toBeTrue()
        ->and($user->hasPlatformAccess())->toBeTrue();
});

test('registration does not start a trial when payments are disabled', function () {
    Mail::fake();
    config(['subscriptions.payments_enabled' => false]);
    $this->seed(SubscriptionPackageSeeder::class);

    $this->post('/register', [
        'name' => 'Free User',
        'email' => 'free-user@example.com',
        'phone' => '0765890922',
        'role' => 'teacher',
        'password' => 'Password1',
        'password_confirmation' => 'Password1',
    ])->assertRedirect(route('dashboard', absolute: false));

    $user = User::query()->where('email', 'free-user@example.com')->first();

    expect($user)->not->toBeNull()
        ->and($user->subscription_tier)->toBeNull()
        ->and($user->trial_used_at)->toBeNull();
});
