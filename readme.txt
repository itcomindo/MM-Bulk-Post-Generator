...
== Description ==

...
Fitur Utama:
*   ...
*   **Pembuatan Custom Field Otomatis**: ...
*   **Integrasi dengan ACF**: ...
*   **Dukungan Shortcode**: Gunakan shortcode seperti `[kota]`, `[alamat]`, `[total_review]` dll. untuk menampilkan data di mana saja.
*   **Schema Markup JSON-LD**: Secara otomatis membuat schema `LocalBusiness` yang valid untuk meningkatkan tampilan di hasil pencarian Google.

...

== Frequently Asked Questions ==

= Apa saja shortcode yang tersedia? =

*   `[judul_post]` - Menampilkan judul post.
*   `[kota]` - Menampilkan kota dari data local business.
*   `[provinsi]` - Menampilkan provinsi.
*   `[kodepos]` - Menampilkan kode pos.
*   `[alamat]` - Menampilkan alamat lengkap yang digenerate.
*   `[nomor_telepon]` - Menampilkan nomor telepon.
*   `[author_review_name]` - Menampilkan nama author review.
*   `[author_rating]` - Menampilkan rating dari author.
*   `[total_review]` - Menampilkan total jumlah review.
*   `[total_average_rating]` - Menampilkan total rata-rata rating.
*   `[pricerange]` - Menampilkan price range.

= Bagaimana cara kerja Schema Markup? =

Saat Anda membuat post menggunakan plugin ini (dengan opsi schema diaktifkan), sebuah custom field akan ditambahkan ke post tersebut. Selama field tersebut ada, JSON-LD Schema akan otomatis ditambahkan ke `<head>` halaman post, bahkan jika plugin MM Bulk Post Generator dinonaktifkan. Anda bisa menonaktifkan schema per post melalui meta box di halaman editor post.

...