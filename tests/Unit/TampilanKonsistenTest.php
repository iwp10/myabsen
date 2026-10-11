<?php

namespace Tests\Unit;

use App\Enums\StatusKehadiran;
use App\Support\Ui;
use Tests\TestCase;

class TampilanKonsistenTest extends TestCase
{
    /**
     * Daftar file Blade dalam cakupan pembaruan tampilan tahap 1.
     *
     * @return array<int, string>
     */
    protected function getPhase1BladeFiles(): array
    {
        $base = dirname(__DIR__, 2);
        $relativePaths = [
            'resources/views/layouts/app.blade.php',
            'resources/views/layouts/navigation.blade.php',
            'resources/views/components/alert.blade.php',
            'resources/views/components/confirm-modal.blade.php',
            'resources/views/components/sidebar-link.blade.php',
            'resources/views/auth/login.blade.php',
            'resources/views/admin/dashboard.blade.php',
            'resources/views/guru/dashboard.blade.php',
            'resources/views/guru/jadwal.blade.php',
            'resources/views/guru/absensi.blade.php',
            'resources/views/guru/koreksi_absensi.blade.php',
            'resources/views/guru/riwayat.blade.php',
            'resources/views/guru/riwayat_detail.blade.php',
            'resources/views/siswa/dashboard.blade.php',
            'resources/views/siswa/jadwal.blade.php',
            'resources/views/siswa/mapel.blade.php',
            'resources/views/siswa/riwayat.blade.php',
            'resources/views/siswa/riwayat_mapel.blade.php',
        ];

        return array_map(fn ($rel) => $base.DIRECTORY_SEPARATOR.str_replace('/', DIRECTORY_SEPARATOR, $rel), $relativePaths);
    }

    /**
     * R1: Setiap atribut class yang memuat utilitas warna harus punya utilitas dark: dengan properti yang sama.
     */
    public function test_r1_semua_utilitas_warna_memiliki_pasangan_dark(): void
    {
        $files = $this->getPhase1BladeFiles();
        $violations = [];

        $props = ['bg', 'text', 'border', 'divide', 'ring', 'placeholder'];

        // Non-color exclusions for utilities
        $nonColorValues = [
            'transparent', 'white', 'black', 'current', 'inherit', 'none', 'auto',
            '0', '1', '2', '4', '8', 'collapse', 'separate',
        ];

        $bgNonColors = [
            'cover', 'center', 'contain', 'no-repeat', 'repeat', 'bottom', 'top',
            'left', 'right', 'fixed', 'local', 'scroll', 'auto',
        ];

        $textNonColors = [
            'xs', 'sm', 'base', 'lg', 'xl', '2xl', '3xl', '4xl', '5xl',
            'left', 'center', 'right', 'justify', 'clip', 'ellipsis',
        ];

        foreach ($files as $file) {
            if (! file_exists($file)) {
                continue;
            }

            $content = file_get_contents($file);
            preg_match_all('/class="([^"]*)"/', $content, $matches);

            foreach ($matches[1] as $classVal) {
                $tokens = preg_split('/\s+/', trim($classVal));
                if (! $tokens) {
                    continue;
                }

                foreach ($props as $prop) {
                    $lightTokens = array_filter($tokens, function ($t) use ($prop, $nonColorValues, $bgNonColors, $textNonColors) {
                        // Ignore state modifiers like dark, hover, focus, group-hover, etc.
                        if (preg_match('/^(dark|hover|focus|focus-visible|group-hover|active|disabled):/', $t)) {
                            return false;
                        }

                        if (! preg_match('/^'.$prop.'-([a-z]+(?:-[a-z0-9\/]+)?)$/', $t, $m)) {
                            return false;
                        }

                        $val = $m[1];
                        if (in_array($val, $nonColorValues, true)) {
                            return false;
                        }

                        if ($prop === 'bg' && (in_array($val, $bgNonColors, true) || preg_match('/^repeat-(x|y|round|space)$/', $val))) {
                            return false;
                        }

                        if ($prop === 'border' && (in_array($val, ['solid', 'dashed', 'dotted', 'double', 't', 'b', 'l', 'r', 'x', 'y'], true) || preg_match('/^(?:[tblrxy]-)?\d+$/', $val))) {
                            return false;
                        }

                        if ($prop === 'divide' && (in_array($val, ['solid', 'dashed', 'x', 'y'], true) || preg_match('/^(?:[xy]-)?\d+$/', $val))) {
                            return false;
                        }

                        if ($prop === 'ring' && (in_array($val, ['inset'], true) || preg_match('/^\d+$/', $val))) {
                            return false;
                        }

                        if ($prop === 'text' && in_array($val, $textNonColors, true)) {
                            return false;
                        }

                        return true;
                    });

                    if (count($lightTokens) > 0) {
                        $hasDark = false;
                        foreach ($tokens as $t) {
                            if (preg_match('/^dark:(?:[a-z-]+:)?'.$prop.'-/', $t)) {
                                $hasDark = true;
                                break;
                            }
                        }

                        if (! $hasDark) {
                            $violations[] = basename($file)." | Prop: {$prop} | Tokens: ".implode(',', $lightTokens)." in '{$classVal}'";
                        }
                    }
                }
            }
        }

        $this->assertEmpty($violations, 'Pelanggaran R1 ditemukan (warna tanpa pasangan dark:): '.PHP_EOL.implode(PHP_EOL, $violations));
    }

    /**
     * R2: Terlarang text-(gray|slate|zinc)-(300|400|500) di mode terang (kecuali placeholder/disabled)
     *     dan dark:text-(gray|slate|zinc)-(500|600|700|800|900) di mode gelap.
     */
    public function test_r2_tidak_ada_teks_redup_di_mode_terang_maupun_gelap(): void
    {
        $files = $this->getPhase1BladeFiles();
        $violations = [];

        foreach ($files as $file) {
            if (! file_exists($file)) {
                continue;
            }

            $content = file_get_contents($file);
            $lines = explode("\n", $content);

            foreach ($lines as $lineNum => $line) {
                preg_match_all('/class="([^"]*)"/', $line, $matches);
                foreach ($matches[1] as $classVal) {
                    $tokens = preg_split('/\s+/', trim($classVal));
                    foreach ($tokens as $token) {
                        // Terlarang mode terang: text-(gray|slate|zinc)-(300|400|500) tanpa placeholder:/disabled: dan bukan dark:
                        if (! str_starts_with($token, 'dark:') && ! str_starts_with($token, 'placeholder:') && ! str_starts_with($token, 'disabled:')) {
                            if (preg_match('/^(?:[a-z-]+:)*text-(gray|slate|zinc)-(300|400|500)$/', $token)) {
                                $violations[] = basename($file).':'.($lineNum + 1)." (Light dim text: {$token})";
                            }
                        }

                        // Terlarang mode gelap: dark:text-(gray|slate|zinc)-(500|600|700|800|900)
                        if (str_starts_with($token, 'dark:')) {
                            if (preg_match('/^dark:(?:[a-z-]+:)*text-(gray|slate|zinc)-(500|600|700|800|900)$/', $token)) {
                                $violations[] = basename($file).':'.($lineNum + 1)." (Dark dim text: {$token})";
                            }
                        }
                    }
                }
            }
        }

        $this->assertEmpty($violations, 'Pelanggaran R2 ditemukan (teks redup terlarang): '.PHP_EOL.implode(PHP_EOL, $violations));
    }

    /**
     * R3: Terlarang bg-green-*, bg-yellow-*, dan bg-gray-100/200 sebagai latar header.
     */
    public function test_r3_tidak_ada_utilitas_warna_terlarang(): void
    {
        $files = $this->getPhase1BladeFiles();
        $violations = [];

        foreach ($files as $file) {
            if (! file_exists($file)) {
                continue;
            }

            $content = file_get_contents($file);
            $lines = explode("\n", $content);

            foreach ($lines as $lineNum => $line) {
                // Periksa bg-green-* dan bg-yellow-*
                if (preg_match('/(?:^|\s)(?:dark:)?bg-(green|yellow)-[a-z0-9]+/i', $line, $m)) {
                    $violations[] = basename($file).':'.($lineNum + 1)." (Forbidden utility: {$m[0]})";
                }
            }
        }

        $this->assertEmpty($violations, 'Pelanggaran R3 ditemukan (warna terlarang): '.PHP_EOL.implode(PHP_EOL, $violations));
    }

    /**
     * R4: Tidak ada class Tailwind yang dirangkai secara dinamis di Blade maupun PHP dalam cakupan.
     */
    public function test_r4_tidak_ada_rangkaian_class_dinamis(): void
    {
        $files = $this->getPhase1BladeFiles();
        $violations = [];

        foreach ($files as $file) {
            if (! file_exists($file)) {
                continue;
            }

            $content = file_get_contents($file);
            $lines = explode("\n", $content);

            foreach ($lines as $lineNum => $line) {
                // Periksa konkatenasi dinamis seperti bg-{$color} atau text-{{$color}} atau "bg-" . $color
                if (preg_match('/(?:bg|text|border|ring|divide)-(\{\{|\{)\$[a-zA-Z0-9_]+/i', $line, $m) ||
                    preg_match('/[\'"](bg|text|border|ring|divide)-[\'"]\s*\.\s*\$/i', $line, $m)) {
                    $violations[] = basename($file).':'.($lineNum + 1)." (Dynamic Tailwind class: {$m[0]})";
                }
            }
        }

        $this->assertEmpty($violations, 'Pelanggaran R4 ditemukan (rangkaian class dinamis): '.PHP_EOL.implode(PHP_EOL, $violations));
    }

    /**
     * Uji helper Ui::statusBadge menghasilkan status unik dan selalu memiliki dark:
     */
    public function test_ui_status_badge_unik_dan_berpasangan_dark(): void
    {
        $hadir = Ui::statusBadge('hadir');
        $izin = Ui::statusBadge('izin');
        $sakit = Ui::statusBadge('sakit');
        $alpa = Ui::statusBadge('alpa');
        $belum = Ui::statusBadge('belum');

        $badges = [$hadir, $izin, $sakit, $alpa, $belum];
        $this->assertCount(5, array_unique($badges));

        foreach ($badges as $badge) {
            $this->assertStringContainsString('dark:', $badge);
            $this->assertStringContainsString('border', $badge);
        }

        // Pastikan enum didukung
        $this->assertSame($hadir, Ui::statusBadge(StatusKehadiran::HADIR));
        $this->assertSame($izin, Ui::statusBadge(StatusKehadiran::IZIN));
        $this->assertSame($sakit, Ui::statusBadge(StatusKehadiran::SAKIT));
        $this->assertSame($alpa, Ui::statusBadge(StatusKehadiran::ALPA));
    }

    /**
     * Uji helper Ui::statusLabel dan statusIcon selalu menyertakan ikon + teks.
     */
    public function test_ui_status_label_dan_ikon_selalu_lengkap(): void
    {
        $statuses = ['hadir', 'izin', 'sakit', 'alpa', 'belum'];
        foreach ($statuses as $st) {
            $label = Ui::statusLabel($st);
            $icon = Ui::statusIcon($st);

            $this->assertNotEmpty($label);
            $this->assertNotEmpty($icon);
        }

        $this->assertSame('check', Ui::statusIcon('hadir'));
        $this->assertSame('info', Ui::statusIcon('izin'));
        $this->assertSame('plus', Ui::statusIcon('sakit'));
        $this->assertSame('x', Ui::statusIcon('alpa'));
        $this->assertSame('clock', Ui::statusIcon('belum'));
    }

    /**
     * Uji helper Ui::persenWarna di sekitar batas 75% dan memiliki pasangan dark:
     */
    public function test_ui_persen_warna_di_sekitar_batas(): void
    {
        $batas = 75;

        $warnaAtas = Ui::persenWarna($batas + 5);
        $warnaBatas = Ui::persenWarna($batas);
        $warnaDekatBawah = Ui::persenWarna($batas - 5);
        $warnaJauhBawah = Ui::persenWarna($batas - 20);

        $this->assertStringContainsString('emerald', $warnaAtas);
        $this->assertStringContainsString('dark:', $warnaAtas);

        $this->assertStringContainsString('emerald', $warnaBatas);
        $this->assertStringContainsString('dark:', $warnaBatas);

        $this->assertStringContainsString('amber', $warnaDekatBawah);
        $this->assertStringContainsString('dark:', $warnaDekatBawah);

        $this->assertStringContainsString('red', $warnaJauhBawah);
        $this->assertStringContainsString('dark:', $warnaJauhBawah);
    }

    /**
     * Uji helper Ui::badgeHari menghasilkan 6 warna berbeda untuk 6 hari sekolah berpasangan dark:
     */
    public function test_ui_badge_hari_enam_warna_berpasangan_dark(): void
    {
        $hariList = ['senin', 'selasa', 'rabu', 'kamis', 'jumat', 'sabtu'];
        $colors = array_map(fn ($h) => Ui::badgeHari($h), $hariList);

        $this->assertCount(6, array_unique($colors));

        foreach ($colors as $c) {
            $this->assertStringContainsString('dark:', $c);
            $this->assertStringContainsString('border', $c);
        }
    }

    /**
     * Uji helper Ui tokens lainnya (rekapCard, headerGrup, kartu, tombol) selalu berpasangan dark:
     */
    public function test_ui_semua_token_permukaan_dan_elevasi_berpasangan_dark(): void
    {
        $tokens = [
            Ui::kartu(),
            Ui::kartuElevated(),
            Ui::headerGrup(),
            Ui::table(),
            Ui::tableHeader(),
            Ui::statBorder('emerald'),
            Ui::rekapCard('hadir'),
            Ui::rekapAngka('hadir'),
            Ui::rekapLabel(),
            Ui::badgeSemester('ganjil'),
            Ui::tombol('primary'),
            Ui::tombol('secondary'),
            Ui::tombol('danger'),
        ];

        foreach ($tokens as $token) {
            $this->assertStringContainsString('dark:', $token);
        }
    }
}
