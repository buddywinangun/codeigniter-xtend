## About Codeigniter Xtend

Codeigniter Xtend adalah alat yang kami rancang sebagai manajemen extension di atas Framework Codeigniter. Ini memungkinkan Anda untuk mengelola (menginstal/memperbarui) extension, seperti: Package, Tema dan Bahasa.

**Codeigniter Xtend memudahkan untuk (mengaktifkan/menonaktifkan) extension**, sehingga semua proyek codeigniter anda dapat menggunakannya kembali.

## Release

Lihat [Release schedule](/CHANGELOG.md#release-schedule) untuk jadwal rilis terbaru. Dan daftar riwayat dapat ditemukan di [CHANGELOG](/CHANGELOG.md#changelog).

## Requirements

-  PHP 7.0.0 or later
-  `composer` command (See [Composer Installation](https://getcomposer.org/doc/00-intro.md#installation-linux-unix-macos))

## Installation

1. Instalasi dilakukan melalui Composer.
```sh
composer require buddywinangun/codeigniter-xtend
```

2. Berikan izin menulis logs, cache, session.
```sh
sudo chmod -R 755 ./application/{logs,cache,session};
sudo chown -R nginx:nginx ./application/{logs,cache,session};
```

## Usage

Lihat [https://codeigniter.com/userguide3/](https://codeigniter.com/userguide3/) untuk penggunaan dasar.

Anda harus memperbarui secara manual jika file di folder `application` atau `index.php` berubah. Cek [CodeIgniter User Guide](http://www.codeigniter.com/user_guide/installation/upgrading.html).

## Unit testing

Tes unit terdiri dari file-file berikut.
- tests/*.php: Test Case.
- phpunit.xml: Test setting fill.
- phpunit-printer.yml: Test result output format.

Jalankan tes.
```sh
composer test
```

## Reference

-  [Composer Installation](https://getcomposer.org/doc/00-intro.md#installation-linux-unix-macos)
-  [CodeIgniter Web Framework](https://codeigniter.com/)

## Contributing

Panduan kontribusi dapat ditemukan di [CONTRIBUTING](.github/CONTRIBUTING.md).

## License

Codeigniter Xtend adalah perangkat lunak sumber terbuka yang dilisensikan di bawah [MIT license](/LICENSE.md).
