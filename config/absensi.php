<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Batas Hari Koreksi Absensi
    |--------------------------------------------------------------------------
    |
    | Menentukan jumlah hari maksimum ke belakang (termasuk hari ini)
    | di mana guru diizinkan untuk mengabsen atau mengoreksi sesi absensi.
    | Contoh: 7 berarti hari ini dan 6 hari sebelumnya.
    |
    */
    'batas_koreksi_hari' => 7,

    /*
    |--------------------------------------------------------------------------
    | Batas Ekspor Laporan
    |--------------------------------------------------------------------------
    |
    | Batas jumlah sheet untuk ekspor Excel multi-sheet dan batas jumlah baris
    | untuk ekspor PDF agar tidak membebani memori server (OOM) dan CPU.
    |
    */
    'batas_sheet_ekspor' => 50,
    'batas_baris_pdf' => 2000,
];
