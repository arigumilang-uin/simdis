<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Informasi Sekolah
    |--------------------------------------------------------------------------
    |
    | Konfigurasi data sekolah yang digunakan di seluruh aplikasi.
    | Perubahan di sini akan otomatis mempengaruhi semua views yang menggunakannya.
    |
    */

    'nama' => env('SCHOOL_NAME', 'SMKN 1 Lubuk Dalam'),
    
    'nama_lengkap' => env('SCHOOL_FULL_NAME', 'SMK Negeri 1 Lubuk Dalam'),
    
    'singkatan' => env('SCHOOL_ABBR', 'SMKN 1 LD'),
    
    'alamat' => env('SCHOOL_ADDRESS', 'Jl. Raya Lubuk Dalam, Kabupaten Siak, Riau'),
    
    'kabupaten' => env('SCHOOL_DISTRICT', 'Kabupaten Siak'),
    
    'provinsi' => env('SCHOOL_PROVINCE', 'Riau'),
    
    'telepon' => env('SCHOOL_PHONE', ''),
    
    'email' => env('SCHOOL_EMAIL', ''),
    
    'website' => env('SCHOOL_WEBSITE', ''),

    /*
    |--------------------------------------------------------------------------
    | Tahun Ajaran
    |--------------------------------------------------------------------------
    |
    | Tahun ajaran aktif. Format: "YYYY/YYYY" (tahun awal/tahun akhir)
    | Dapat diubah via .env atau langsung di sini.
    |
    */

    'tahun_ajaran' => env('SCHOOL_YEAR', '2026/2027'),

    /*
    |--------------------------------------------------------------------------
    | Kepala Sekolah
    |--------------------------------------------------------------------------
    */

    'kepala_sekolah' => [
        'nama' => env('PRINCIPAL_NAME', ''),
        'nip' => env('PRINCIPAL_NIP', ''),
    ],

    /*
    |--------------------------------------------------------------------------
    | Sistem Informasi
    |--------------------------------------------------------------------------
    */

    'sistem' => [
        'nama' => 'IDEAL',
        'nama_lengkap' => 'Integrated Discipline & Educational Achievement Log',
        'versi' => '1.0.0',
    ],

];
