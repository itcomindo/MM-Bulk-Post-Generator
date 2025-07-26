=== MM Bulk Post Generator ===
Contributors: Budi Haryono
Tags: bulk post, post generator, spintax, local seo, custom fields, automation
Requires at least: 5.0
Tested up to: 6.4
Stable tag: 1.0.0
Requires PHP: 7.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Plugin untuk membuat post secara massal dengan dukungan spintax, data Local Business, dan pembuatan custom field secara otomatis.

== Description ==

MM Bulk Post Generator adalah plugin yang dirancang untuk membantu Anda membuat ratusan atau ribuan post unik secara otomatis. Plugin ini sangat cocok untuk kampanye SEO Lokal, di mana Anda perlu menargetkan banyak kota atau area dengan konten yang serupa tapi tetap unik.

Fitur Utama:
*   **Generate Post dari Daftar Lokasi**: Cukup masukan daftar lokasi (Kota, Provinsi, Kode Pos), dan plugin akan membuat satu post untuk setiap lokasi.
*   **Dukungan Spintax**: Gunakan format spintax `{pilihan1|pilihan2}` di judul dan konten untuk menghasilkan variasi tulisan yang tak terbatas. Mendukung nested spintax.
*   **Penjadwalan Acak**: Tentukan rentang tanggal mulai dan selesai, dan setiap post akan dipublikasikan pada waktu yang acak di dalam rentang tersebut.
*   **Featured Image Acak**: Pilih beberapa gambar, dan plugin akan menetapkan satu gambar secara acak sebagai featured image untuk setiap post.
*   **Pembuatan Custom Field Otomatis**: Plugin secara otomatis membuat dan mengisi custom field penting untuk SEO Lokal (kota, provinsi, kodepos, alamat lengkap, data review, dll).
*   **Integrasi dengan ACF**: Jika Anda menggunakan Advanced Custom Fields (ACF), plugin ini akan secara otomatis mendaftarkan field group "MM SEO Local Business" sehingga field-field tersebut tertata rapi di halaman editor post.

== Installation ==

1.  Upload folder `mm-bulk-post-generator` ke direktori `/wp-content/plugins/`.
2.  Aktifkan plugin melalui menu 'Plugins' di WordPress.
3.  Akses halaman generator melalui menu "Bulk Post Gen" di sidebar admin.

== How to Use ==

1.  **Install & Aktivasi**: Ikuti langkah instalasi di atas.
2.  **Buka Halaman Generator**: Pergi ke menu "Bulk Post Gen" di dashboard Anda.
3.  **Isi Local Business Target**: Masukkan daftar target Anda dengan format `Kota,Provinsi,KodePos` per baris. Contoh: `Surabaya,Jawa Timur,60111`.
4.  **Isi Judul Post**: Tulis judul dengan format spintax dan gunakan placeholder `[kota]` dan `[provinsi]`. Contoh: `{Jasa|Ahli|Layanan} SEO Terbaik di [kota]`.
5.  **Isi Artikel**: Tulis konten artikel di editor, Anda juga bisa menggunakan spintax di sini.
6.  **Pilih Featured Images**: Klik tombol "Pilih Gambar" dan pilih beberapa gambar dari Media Library.
7.  **Tentukan Jadwal**: Pilih tanggal mulai dan selesai untuk penjadwalan post.
8.  **Pilih Kategori**: Pilih satu kategori untuk semua post yang akan dibuat.
9.  **Isi Field Lainnya**: Isi tag (opsional) dan nomor telepon jika perlu.
10. **Klik START GENERATE**: Tombol akan aktif setelah semua field yang wajib diisi telah lengkap. Tunggu hingga proses selesai.

== Changelog ==

= 1.0.0 =
* Initial release.