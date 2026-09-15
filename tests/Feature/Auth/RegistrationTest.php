<?php

test('registration screen can be rendered', function () {
    $response = $this->get('/register');

    $response->assertStatus(200);
});

test('new users can register', function () {
    $response = $this->post('/register', [
        'account_type' => 'customer',
        'name' => 'Test User',
        'email' => 'test@example.com',
        'password' => 'Str0ngPass',
        'password_confirmation' => 'Str0ngPass',
        'terms' => '1',
    ]);

    $this->assertAuthenticated();
    $response->assertRedirect(route('account.dashboard', absolute: false));
});
