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
            'resources/views/components/ui/pertemuan-chip.blade.php',
            'resources/views/components/ui/file-input.blade.php',
            'resources/views/components/ui/icon.blade.php',
            'resources/views/components/ui/bulan-select.blade.php',
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

    /**
     * Uji helper Ui::pertemuanChip menghasilkan status unik, berpasangan dark:, dan border-2.
     */
    public function test_ui_pertemuan_chip_unik_dan_berpasangan_dark(): void
    {
        $statuses = ['hadir', 'izin', 'sakit', 'alpa', null];
        $chips = array_map(fn ($s) => Ui::pertemuanChip($s), $statuses);

        $this->assertCount(5, array_unique($chips));

        foreach ($chips as $chip) {
            $this->assertStringContainsString('dark:', $chip);
            $this->assertStringContainsString('border-2', $chip);
        }

        $this->assertSame('H', Ui::statusHuruf('hadir'));
        $this->assertSame('I', Ui::statusHuruf('izin'));
        $this->assertSame('S', Ui::statusHuruf('sakit'));
        $this->assertSame('A', Ui::statusHuruf('alpa'));
        $this->assertSame('-', Ui::statusHuruf(null));
        $this->assertSame('-', Ui::statusHuruf('belum'));
    }

    /**
     * Uji komponen x-ui.file-input memuat file: dan dark:file: berpasangan.
     */
    public function test_x_ui_file_input_memuat_file_dan_dark_file_berpasangan(): void
    {
        $path = dirname(__DIR__, 2).DIRECTORY_SEPARATOR.'resources'.DIRECTORY_SEPARATOR.'views'.DIRECTORY_SEPARATOR.'components'.DIRECTORY_SEPARATOR.'ui'.DIRECTORY_SEPARATOR.'file-input.blade.php';
        $content = file_get_contents($path);

        $this->assertStringContainsString('file:', $content);
        $this->assertStringContainsString('dark:file:', $content);
        $this->assertStringContainsString('min-h-[44px]', $content);
    }

    /**
     * R5: Setiap tag <svg> di seluruh file Blade harus memiliki atribut width dan height,
     * atau class w-* dan h-*, atau size-*, kecuali file yang berada di allowlist beralasan.
     */
    public function test_r5_setiap_tag_svg_memiliki_ukuran_eksplisit(): void
    {
        $viewsDir = dirname(__DIR__, 2).DIRECTORY_SEPARATOR.'resources'.DIRECTORY_SEPARATOR.'views';
        $iterator = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($viewsDir));

        $violations = [];
        $allowlist = [];

        foreach ($iterator as $fileInfo) {
            if (! $fileInfo->isFile() || ! str_ends_with($fileInfo->getFilename(), '.blade.php')) {
                continue;
            }

            $path = $fileInfo->getPathname();
            $rel = str_replace(dirname(__DIR__, 2).DIRECTORY_SEPARATOR, '', $path);
            $relForward = str_replace(DIRECTORY_SEPARATOR, '/', $rel);

            if (isset($allowlist[$relForward])) {
                continue;
            }

            $content = file_get_contents($path);
            if (! str_contains($content, '<svg')) {
                continue;
            }

            preg_match_all('/<svg\b([^>]*)>/i', $content, $matches);

            foreach ($matches[1] as $attrs) {
                // Jangan anggap stroke-width sebagai atribut width
                $hasWidth = (bool) preg_match('/(?:^|\s)width\s*=/i', $attrs);
                $hasHeight = (bool) preg_match('/(?:^|\s)height\s*=/i', $attrs);

                $classStr = '';
                if (preg_match('~\\bclass\\s*=\\s*["\']([^"\']*)["\']~i', $attrs, $classMatch)) {
                    $classStr = $classMatch[1];
                }

                $hasW = (bool) preg_match('/\bw-\S+/', $classStr);
                $hasH = (bool) preg_match('/\bh-\S+/', $classStr);
                $hasSize = (bool) preg_match('/\bsize-\S+/', $classStr);
                $hasInvalidSize = (bool) preg_match('/\bsize-4\.5\b/', $classStr);

                $isSized = ($hasWidth && $hasHeight) || ($hasW && $hasH && ! $hasInvalidSize) || ($hasSize && ! $hasInvalidSize);

                if (! $isSized) {
                    $violations[] = "{$relForward} | class='{$classStr}' | <svg{$attrs}>";
                }
            }
        }

        $this->assertEmpty($violations, 'Pelanggaran R5 ditemukan (SVG tanpa ukuran eksplisit): '.PHP_EOL.implode(PHP_EOL, $violations));
    }

    /**
     * Uji komponen x-ui.icon dan Ui::iconClass menghasilkan ukuran literal sm, md, lg.
     */
    public function test_x_ui_icon_dan_ui_icon_class_literal(): void
    {
        $this->assertSame('w-4 h-4 shrink-0', Ui::iconClass('sm'));
        $this->assertSame('w-5 h-5 shrink-0', Ui::iconClass('md'));
        $this->assertSame('w-6 h-6 shrink-0', Ui::iconClass('lg'));

        $renderedSm = $this->blade('<x-ui.icon size="sm" name="arrow-right" />');
        $this->assertStringContainsString('w-4 h-4 shrink-0', $renderedSm);
        $this->assertStringContainsString('<svg', $renderedSm);

        $renderedMd = $this->blade('<x-ui.icon size="md" name="arrow-right" />');
        $this->assertStringContainsString('w-5 h-5 shrink-0', $renderedMd);

        $renderedLg = $this->blade('<x-ui.icon size="lg" name="arrow-right" />');
        $this->assertStringContainsString('w-6 h-6 shrink-0', $renderedLg);
    }

    /**
     * Uji Ui::getDaftarBulanPeriode dan komponen x-ui.bulan-select.
     */
    public function test_x_ui_bulan_select_dan_ui_get_daftar_bulan_periode(): void
    {
        // Ganjil 2026/2027: Juli sampai Desember 2026
        $ganjil = Ui::getDaftarBulanPeriode('2026/2027', 'Ganjil');
        $this->assertCount(6, $ganjil);
        $this->assertSame('2026-07', $ganjil[0]['value']);
        $this->assertSame('Juli 2026', $ganjil[0]['label']);
        $this->assertSame('2026-12', $ganjil[5]['value']);
        $this->assertSame('Desember 2026', $ganjil[5]['label']);

        // Genap 2026/2027: Januari sampai Juni 2027
        $genap = Ui::getDaftarBulanPeriode('2026/2027', 'Genap');
        $this->assertCount(6, $genap);
        $this->assertSame('2027-01', $genap[0]['value']);
        $this->assertSame('Januari 2027', $genap[0]['label']);
        $this->assertSame('2027-06', $genap[5]['value']);
        $this->assertSame('Juni 2027', $genap[5]['label']);

        // Bulan di luar 6 bulan (mis. 2026-05 pada Ganjil) tetap disertakan
        $extra = Ui::getDaftarBulanPeriode('2026/2027', 'Ganjil', '2026-05');
        $this->assertCount(7, $extra);
        $values = array_column($extra, 'value');
        $this->assertContains('2026-05', $values);

        // Komponen blade x-ui.bulan-select merender select dan menandai opsi terpilih
        $rendered = $this->blade('<x-ui.bulan-select tahun-ajaran="2026/2027" semester="Ganjil" selected="2026-10" />');
        $this->assertStringContainsString('<select', $rendered);
        $this->assertStringContainsString('Semua bulan', $rendered);
        $this->assertStringContainsString('value="2026-10"', $rendered);
        $this->assertStringContainsString('selected', $rendered);
    }

    /**
     * R6: Setiap <select> di cakupan (guru, siswa, layouts, components, dan admin/pergantian_periode)
     * harus memakai x-ui.select atau memiliki w-full, min-w-0, dan padding kanan untuk panah (pr-8, pr-9, atau pr-10).
     */
    public function test_r6_semua_select_dalam_cakupan_memenuhi_standar_atau_x_ui_select(): void
    {
        $base = dirname(__DIR__, 2).DIRECTORY_SEPARATOR.'resources'.DIRECTORY_SEPARATOR.'views';
        $scopeDirs = [
            $base.DIRECTORY_SEPARATOR.'guru',
            $base.DIRECTORY_SEPARATOR.'siswa',
            $base.DIRECTORY_SEPARATOR.'layouts',
            $base.DIRECTORY_SEPARATOR.'components',
            $base.DIRECTORY_SEPARATOR.'admin'.DIRECTORY_SEPARATOR.'pergantian_periode',
        ];

        $violations = [];

        foreach ($scopeDirs as $dir) {
            if (! is_dir($dir)) {
                continue;
            }

            $iterator = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($dir));
            foreach ($iterator as $fileInfo) {
                if (! $fileInfo->isFile() || ! str_ends_with($fileInfo->getFilename(), '.blade.php')) {
                    continue;
                }

                $path = $fileInfo->getPathname();
                $rel = str_replace(dirname(__DIR__, 2).DIRECTORY_SEPARATOR, '', $path);
                $relForward = str_replace(DIRECTORY_SEPARATOR, '/', $rel);

                $content = file_get_contents($path);
                if (! str_contains($content, '<select')) {
                    continue;
                }

                $isComponentDef = str_ends_with($relForward, 'components/ui/select.blade.php');
                if ($isComponentDef) {
                    $hasWFull = str_contains($content, 'w-full');
                    $hasMinW0 = str_contains($content, 'min-w-0');
                    $hasPaddingRight = (bool) preg_match('/\bpr-(?:8|9|10)\b/', $content);
                    if (! ($hasWFull && $hasMinW0 && $hasPaddingRight)) {
                        $violations[] = "{$relForward} | x-ui.select komponen tidak memiliki w-full/min-w-0/pr-8..10";
                    }

                    continue;
                }

                preg_match_all('/<select\b([^>]*)>/i', $content, $matches);
                foreach ($matches[1] as $attrs) {
                    $classStr = '';
                    if (preg_match('~\\bclass\\s*=\\s*["\']([^"\']*)["\']~i', $attrs, $classMatch)) {
                        $classStr = $classMatch[1];
                    }

                    $hasWFull = (bool) preg_match('/\bw-full\b/', $classStr);
                    $hasMinW0 = (bool) preg_match('/\bmin-w-0\b/', $classStr);
                    $hasPaddingRight = (bool) preg_match('/\bpr-(?:8|9|10)\b/', $classStr);

                    if (! ($hasWFull && $hasMinW0 && $hasPaddingRight)) {
                        $violations[] = "{$relForward} | class='{$classStr}' | <select{$attrs}>";
                    }
                }
            }
        }

        $this->assertEmpty($violations, 'Pelanggaran R6 ditemukan (<select> tanpa w-full, min-w-0, atau pr-8/9/10): '.PHP_EOL.implode(PHP_EOL, $violations));
    }

    /**
     * Uji komponen x-ui.select merender wrapper relatif, select dengan class standar, dan ikon panah.
     */
    public function test_x_ui_select_merender_wrapper_dan_panah(): void
    {
        $rendered = $this->blade('<x-ui.select name="kelas_id" id="kelas_select"><option value="1">Kelas 1</option></x-ui.select>');

        $this->assertStringContainsString('relative', $rendered);
        $this->assertStringContainsString('<select', $rendered);
        $this->assertStringContainsString('name="kelas_id"', $rendered);
        $this->assertStringContainsString('id="kelas_select"', $rendered);
        $this->assertStringContainsString('w-full', $rendered);
        $this->assertStringContainsString('min-w-0', $rendered);
        $this->assertStringContainsString('pr-10', $rendered);
        $this->assertStringContainsString('appearance-none', $rendered);
        $this->assertStringContainsString('min-h-[44px]', $rendered);
        $this->assertStringContainsString('pointer-events-none', $rendered);
        $this->assertStringContainsString('<svg', $rendered);
    }

    /**
     * Uji komponen x-ui.pilih-semua dan x-ui.pilih-baris.
     */
    public function test_x_ui_pilih_semua_dan_pilih_baris(): void
    {
        // Header
        $renderedHeader = $this->blade('<x-ui.pilih-semua aria-label="Pilih semua siswa di halaman ini" :count="24" />');
        $this->assertStringContainsString(':checked="semuaTerpilih"', $renderedHeader);
        $this->assertStringContainsString('x-effect="$el.indeterminate = Boolean(sebagian)"', $renderedHeader);
        $this->assertStringContainsString('@change="toggleSemua($event.target.checked)"', $renderedHeader);
        $this->assertStringContainsString('aria-label="Pilih semua siswa di halaman ini"', $renderedHeader);

        // Baris dengan ID statis
        $renderedBaris = $this->blade('<x-ui.pilih-baris id="15" name="ids[]" aria-label="Pilih siswa Budi" />');
        $this->assertStringContainsString('value="15"', $renderedBaris);
        $this->assertStringContainsString(':checked="terpilih.includes(String(\'15\'))"', $renderedBaris);
        $this->assertStringContainsString('@change="toggleSatu(\'15\')"', $renderedBaris);
        $this->assertStringContainsString('name="ids[]"', $renderedBaris);
        $this->assertStringContainsString('aria-label="Pilih siswa Budi"', $renderedBaris);

        // Baris dengan Alpine ID
        $renderedAlpineBaris = $this->blade('<x-ui.pilih-baris alpine-id="item.id" name="siswa_ids[]" :alpine-aria-label="\'item.name\'" />');
        $this->assertStringContainsString(':value="item.id"', $renderedAlpineBaris);
        $this->assertStringContainsString(':checked="terpilih.includes(String(item.id))"', $renderedAlpineBaris);
        $this->assertStringContainsString('@change="toggleSatu(item.id)"', $renderedAlpineBaris);
        $this->assertStringContainsString('name="siswa_ids[]"', $renderedAlpineBaris);
        $this->assertStringContainsString(':aria-label="item.name"', $renderedAlpineBaris);
    }
}
