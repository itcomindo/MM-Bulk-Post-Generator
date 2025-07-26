<?php
if (! defined('ABSPATH')) exit;

// Daftar nama jalan di Indonesia
function mmbpg_get_random_street()
{
    $streets = [
        'Jl. Pahlawan',
        'Jl. Merdeka',
        'Jl. Sudirman',
        'Jl. Thamrin',
        'Jl. Gajah Mada',
        'Jl. Hayam Wuruk',
        'Jl. Diponegoro',
        'Jl. Imam Bonjol',
        'Jl. Gatot Subroto',
        'Jl. S. Parman',
        'Jl. Anggrek',
        'Jl. Melati',
        'Jl. Mawar',
        'Jl. Kamboja',
        'Jl. Cempaka',
        'Jl. Kenanga',
        'Jl. Flamboyan',
        'Jl. Bougenville',
        'Jl. Tulip',
        'Jl. Dahlia',
        'Jl. Elang',
        'Jl. Rajawali',
        'Jl. Merpati',
        'Jl. Garuda',
        'Jl. Cendrawasih',
        'Jl. Harimau',
        'Jl. Singa',
        'Jl. Macan',
        'Jl. Kancil',
        'Jl. Gajah',
        'Jl. Apel',
        'Jl. Mangga',
        'Jl. Durian',
        'Jl. Rambutan',
        'Jl. Jeruk',
        'Jl. Salak',
        'Jl. Nangka',
        'Jl. Pepaya',
        'Jl. Semangka',
        'Jl. Anggur',
        'Jl. Semeru',
        'Jl. Merapi',
        'Jl. Bromo',
        'Jl. Rinjani',
        'Jl. Krakatau',
        'Jl. Pattimura',
        'Jl. Teuku Umar',
        'Jl. Cut Nyak Dien',
        'Jl. Kartini',
        'Jl. Ki Hajar Dewantara'
    ];
    return $streets[array_rand($streets)];
}

// Daftar nama orang Indonesia
function mmbpg_get_random_name()
{
    $names = [
        'Budi Santoso',
        'Ani Wijaya',
        'Eko Prasetyo',
        'Siti Aminah',
        'Dewi Lestari',
        'Muhammad Rizki',
        'Putri Ayu',
        'Agus Setiawan',
        'Indah Permata',
        'Joko Susilo',
        'Rina Marlina',
        'Dedi Haryanto',
        'Maya Sari',
        'Fajar Nugroho',
        'Lia Amelia',
        'Hendra Gunawan',
        'Fitriani',
        'Ahmad Fauzi',
        'Widya Astuti',
        'Tri Hartono',
        'Yulia Puspita',
        'Doni Saputra',
        'Citra Kirana',
        'Bayu Adjie',
        'Nurul Hidayah',
        'Reza Pahlevi',
        'Dian Novita',
        'Galih Prakoso',
        'Wulan Dari',
        'Irfan Hakim',
        'Siska Pratiwi',
        'Rian Ardianto',
        'Tika Ramlan',
        'Andi Suherman',
        'Eva Yolanda',
        'Zainal Abidin',
        'Mega Utami',
        'Ferry Irawan',
        'Ratna Komala',
        'Dimas Anggara',
        'Chandra Kusuma',
        'Lulu Tobing',
        'Guntur Wibowo',
        'Nadia Vega',
        'Aditya Pratama',
        'Eka Kurniawan',
        'Sari Ningsih',
        'Surya Saputra',
        'Intan Baiduri',
        'Ricky Harun'
    ];
    return $names[array_rand($names)];
}

// Menghasilkan tanggal acak dalam rentang tertentu
function mmbpg_get_random_date($start_date, $end_date)
{
    $min = strtotime($start_date);
    $max = strtotime($end_date);
    $rand_time = mt_rand($min, $max);
    return date('Y-m-d H:i:s', $rand_time);
}

// Menghasilkan angka float acak dalam rentang tertentu
function mmbpg_get_random_float($min, $max, $decimals = 1)
{
    $scale = pow(10, $decimals);
    return mt_rand($min * $scale, $max * $scale) / $scale;
}
