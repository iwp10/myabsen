<?php

use Illuminate\Database\Eloquent\Model;

test('lazy loading is prevented in non-production environments', function () {
    expect(Model::preventsLazyLoading())->toBeTrue();
});

test('validation messages are translated to indonesian', function () {
    $response = $this->post('/login', [
        'username' => '',
        'password' => '',
    ]);

    $response->assertSessionHasErrors(['username', 'password']);
    $errors = session('errors')->getMessages();

    expect($errors['username'][0])->toContain('wajib diisi');
    expect($errors['password'][0])->toContain('wajib diisi');
});

test('404 error page returns indonesian friendly message', function () {
    $response = $this->get('/halaman-pasti-tidak-ada-404');

    $response->assertStatus(404);
    $response->assertSee('404');
    $response->assertSee('Halaman Tidak Ditemukan');
    $response->assertSee('Maaf, tautan yang Anda tuju tidak tersedia');
});

test('500 error page template contains indonesian friendly message', function () {
    $view = view('errors.500')->render();

    expect($view)->toContain('500');
    expect($view)->toContain('Terjadi Kesalahan Server');
    expect($view)->toContain('kendala teknis internal');
});
