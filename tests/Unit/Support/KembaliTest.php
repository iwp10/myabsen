<?php

namespace Tests\Unit\Support;

use App\Support\Kembali;
use PHPUnit\Framework\TestCase;

class KembaliTest extends TestCase
{
    public function test_menerima_jalur_relatif_yang_diizinkan_dengan_query(): void
    {
        $validUrls = [
            '/admin/jadwal',
            '/admin/jadwal?hari=senin',
            '/admin/jadwal?periode=2024%2F2025%7CGanjil&page=3',
            '/admin/kelas',
            '/admin/kelas?tingkat=10&page=2',
            '/admin/jadwal/123/edit',
        ];

        foreach ($validUrls as $url) {
            $this->assertTrue(Kembali::isValid($url), "Harusnya valid: {$url}");
            $this->assertSame($url, Kembali::tujuan($url, '/admin/jadwal'));
        }
    }

    public function test_menolak_skema_dan_open_redirect(): void
    {
        $invalidUrls = [
            'https://evil.com/admin/jadwal',
            'http://attacker.org/admin/kelas',
            '//evil.com/admin/jadwal',
            'javascript:alert(1)',
            'data:text/html,<html>',
            '/\\evil.com/admin/jadwal',
            '\\admin\\jadwal',
            'ftp://example.com/admin/jadwal',
        ];

        foreach ($invalidUrls as $url) {
            $this->assertFalse(Kembali::isValid($url), "Harusnya ditolak: {$url}");
            $this->assertSame('/fallback', Kembali::tujuan($url, '/fallback'));
        }
    }

    public function test_menolak_prefix_yang_tidak_diizinkan(): void
    {
        $disallowedUrls = [
            '/admin/users',
            '/admin/jadwal-palsu',
            '/admin/kelaspalsu',
            '/guru/jadwal',
            '/siswa/riwayat',
            '/',
        ];

        foreach ($disallowedUrls as $url) {
            $this->assertFalse(Kembali::isValid($url), "Harusnya ditolak karena prefix tidak terdaftar: {$url}");
            $this->assertSame('/fallback', Kembali::tujuan($url, '/fallback'));
        }
    }

    public function test_menolak_karakter_kontrol_dan_panjang_berlebih(): void
    {
        $controlCharUrl = "/admin/jadwal\x00?hack=true";
        $this->assertFalse(Kembali::isValid($controlCharUrl));

        $longUrl = '/admin/jadwal?q='.str_repeat('a', 2005);
        $this->assertFalse(Kembali::isValid($longUrl));

        $this->assertSame('/fallback', Kembali::tujuan($controlCharUrl, '/fallback'));
        $this->assertSame('/fallback', Kembali::tujuan($longUrl, '/fallback'));
    }

    public function test_menangani_null_dan_string_kosong(): void
    {
        $this->assertFalse(Kembali::isValid(null));
        $this->assertFalse(Kembali::isValid(''));
        $this->assertFalse(Kembali::isValid('   '));

        $this->assertSame('/admin/jadwal', Kembali::tujuan(null, '/admin/jadwal'));
        $this->assertSame('/admin/jadwal', Kembali::tujuan('', '/admin/jadwal'));
    }
}
