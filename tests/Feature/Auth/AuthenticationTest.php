<?php

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Session\TokenMismatchException;
use Illuminate\Support\Facades\Auth;

test('login screen can be rendered', function () {
    $response = $this->get('/login');

    $response->assertStatus(200);
});

test('users can authenticate using the login screen', function () {
    $user = User::factory()->create();

    $response = $this->post('/login', [
        'email' => $user->email,
        'password' => 'password',
    ]);

    $this->assertAuthenticated();
    $response->assertRedirect(route('dashboard', absolute: false));
});

test('users can not authenticate with invalid password', function () {
    $user = User::factory()->create();

    $this->post('/login', [
        'email' => $user->email,
        'password' => 'wrong-password',
    ]);

    $this->assertGuest();
});

test('users can logout', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->post('/logout');

    $this->assertGuest();
    $response->assertRedirect('/login');
});

test('token mismatch on login redirects guest back to login with status', function () {
    $request = Request::create('/login', 'POST');
    $this->startSession();
    $request->setLaravelSession($this->app['session']->driver());

    $response = $this->app->make(\Illuminate\Contracts\Debug\ExceptionHandler::class)
        ->render($request, new TokenMismatchException('CSRF token mismatch.'));

    expect($response->isRedirect())->toBeTrue()
        ->and($response->headers->get('Location'))->toEndWith('/login');
    expect(session('status'))->toBeString()->not->toBeEmpty();
});

test('token mismatch on login redirects authenticated user to dashboard', function () {
    $user = User::factory()->create(['role' => 'teacher']);
    Auth::login($user);

    $request = Request::create('/login', 'POST');
    $this->startSession();
    $request->setLaravelSession($this->app['session']->driver());
    $request->setUserResolver(fn () => $user);

    $response = $this->app->make(\Illuminate\Contracts\Debug\ExceptionHandler::class)
        ->render($request, new TokenMismatchException('CSRF token mismatch.'));

    expect($response->isRedirect())->toBeTrue()
        ->and($response->headers->get('Location'))->toEndWith('/dashboard');
});
