## About Codeigniter Xtend

Codeigniter Xtend adalah alat yang kami rancang sebagai manajemen extension di atas Framework Codeigniter. Ini memungkinkan Anda untuk mengelola (menginstal/memperbarui) extension, seperti:

-  Bahasa, anda dapat mengelola (menginstal/memperbarui) bahasa.
-  Package, anda dapat mengelola (menginstal/memperbarui) package.

**Codeigniter Xtend memudahkan untuk mengaktifkan dan menonaktifkan extension**, sehingga semua proyek codeigniter anda dapat menggunakannya kembali.

## Release

Lihat [Release schedule](/docs/release.md#release-schedule) untuk jadwal rilis terbaru.

## Requirements

-  PHP 7.0.0 or later
-  `composer` command (See [Composer Installation](https://getcomposer.org/doc/00-intro.md#installation-linux-unix-macos))

## Installation

Codeigniter Xtend menginstal [CodeIgniter Framework](https://github.com/bcit-ci/CodeIgniter) resmi (versi `3.1.*`) melalui Composer.

1. Create a project of **Codeigniter Xtend**.

```
composer create-project buddywinangun/codeigniter-xtend my-project
```

2.Grant write permission to the system directory
```
sudo chmod -R 777 ./application/{logs,cache,session};
sudo chown -R nginx:nginx ./application/{logs,cache,session};
```

## Usage

Lihat [https://codeigniter.com/userguide3/](https://codeigniter.com/userguide3/) untuk penggunaan dasar.

Anda harus memperbarui secara manual jika file di folder `application` atau `index.php` berubah. Cek [CodeIgniter User Guide](http://www.codeigniter.com/user_guide/installation/upgrading.html).

## Reference

-  [Composer Installation](https://getcomposer.org/doc/00-intro.md#installation-linux-unix-macos)
-  [CodeIgniter](https://github.com/bcit-ci/CodeIgniter)

## Contributing

Panduan kontribusi dapat ditemukan di [CONTRIBUTING](.github/CONTRIBUTING.md).

## License

Codeigniter Xtend adalah perangkat lunak sumber terbuka yang dilisensikan di bawah [MIT license](/LICENSE.md).
