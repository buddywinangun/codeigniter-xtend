## About Codeigniter Xtend Appstarter

Codeigniter Xtend adalah alat yang kami rancang sebagai manajemen extension di atas Framework Codeigniter. Ini memungkinkan Anda untuk mengelola (menginstal/memperbarui) extension, seperti: Package, Tema dan Bahasa.

**Codeigniter Xtend memudahkan untuk (mengaktifkan/menonaktifkan) extension**, sehingga semua proyek codeigniter anda dapat menggunakannya kembali.

## Usage

Configure your web server or virtual host so that your project's
document root maps to this **public** folder inside your project.

If you wish to run your app from the command line, for testing,
then the following, from your project root, would run it on port 8000:
```sh
cd public
php -S localhost:8000
```

## Installation

This project is meant to be composer-installed:
```sh
composer create-project buddywinangun/codeigniter-xtend
```

Berikan izin menulis logs, cache, session.
```sh
sudo chmod -R 755 ./application/{logs,cache,session};
sudo chown -R nginx:nginx ./application/{logs,cache,session};
```

Updates to the framework can then be incorporated with
```sh
composer update
```
