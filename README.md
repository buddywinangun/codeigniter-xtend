## About Codeigniter Xtend

Codeigniter Xtend adalah alat yang kami rancang sebagai manajemen extension di atas Framework Codeigniter. Ini memungkinkan Anda untuk mengelola (menginstal/memperbarui) extension, seperti:

-  Bahasa, anda dapat mengelola (menginstal/memperbarui) bahasa.
-  Package, anda dapat mengelola (menginstal/memperbarui) package.

**Codeigniter Xtend memudahkan untuk mengaktifkan dan menonaktifkan extension**, sehingga semua proyek codeigniter anda dapat menggunakannya kembali.

Codeigniter Xtend menginstal [CodeIgniter Framework](https://github.com/bcit-ci/CodeIgniter) resmi (versi `3.1.*`) melalui Composer.

## Release

Lihat [Release schedule](/docs/release.md#release-schedule) untuk jadwal rilis terbaru.

## Requirements

- PHP 7.0.0 or later
-  `composer` command (See [Composer Installation](https://getcomposer.org/doc/00-intro.md#installation-linux-unix-macos))

## Installation

1. Clone Repo **Codeigniter Xtend**.

```
https://github.com/buddywinangun/codeigniter-xtend.git
```

2. Di baris bash/terminal/command, `cd` ke dalam direktori proyek Anda.
3. Jalankan `composer install` untuk menginstal dependensi yang diperlukan.

```
composer install
```

## Usage

Lihat [https://codeigniter.com/userguide3/](https://codeigniter.com/userguide3/) untuk penggunaan dasar.

Ubah `application/config/config.php`:
<table>
<thead>
<tr>
<th>Name</th>
<th>Before</th>
<th>After</th>
</tr>
</thead>
<tbody>
<tr>
<td>base*url</td>
<td></td>
<td>if (!empty($_SERVER['HTTP_HOST'])) $config['base_url'] = '//' . $_SERVER['HTTP_HOST'] . str_replace(basename($\_SERVER['SCRIPT_NAME']), '', $\_SERVER['SCRIPT_NAME']);</td>
</tr>
<tr>
<td>enable_hooks</td>
<td>FALSE</td>
<td>TRUE</td>
</tr>
<tr>
<td>permitted_uri_chars</td>
<td>a-z 0-9~%.:*\-</td>
<td>a-z 0-9~%.:\_\-,</td>
</tr>
<tr>
<td>sess_save_path</td>
<td>NULL</td>
<td>APPPATH . 'session';</td>
</tr>
<tr>
<td>cookie_httponly</td>
<td>FALSE</td>
<td>TRUE</td>
</tr>
<tr>
<td>composer_autoload</td>
<td>FALSE</td>
<td>realpath(APPPATH . '../vendor/autoload.php');</td>
</tr>
<tr>
<td>index_page</td>
<td>index.php</td>
<td></td>
</tr>
</tbody>
</table>

Anda harus memperbarui file secara manual jika file di folder `application` atau `index.php` berubah. Cek [CodeIgniter User Guide](http://www.codeigniter.com/user_guide/installation/upgrading.html).

## Reference

-  [Composer Installation](https://getcomposer.org/doc/00-intro.md#installation-linux-unix-macos)
-  [CodeIgniter](https://github.com/bcit-ci/CodeIgniter)

## Contributing

Panduan kontribusi dapat ditemukan di [CONTRIBUTING](.github/CONTRIBUTING.md).

## License

Codeigniter Xtend adalah perangkat lunak sumber terbuka yang dilisensikan di bawah [MIT license](/LICENSE.md).
