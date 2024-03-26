# Release

This repository is a monorepo containing the entirety of the web framework.
Codeigniter Xtend can be used as a full stack framework or as standalone packages which
can be used independently.

## Release schedule

| Release | Status          | Initial Release | Active LTS Start | Maintenance LTS Start | End-of-life |
| :-----: | :-------------: | :-------------: | :--------------: | :-------------------: | :---------: |
| [1.x][] | **Active LTS**  | 2018-08-17      | 2018-12-12       | TBD                   | TBD         |
| [2.x][] | **Current**     | 2018-12-26      | TBD              | TBD                   | TBD         |
| 3.x     | **Pending**     | TBD             | TBD              | TBD                   | TBD         |

[1.x]: https://github.com/buddywinangun/codeigniter-xtend/tree/1.0.0
[2.x]: https://github.com/buddywinangun/codeigniter-xtend/tree/2.0.0

## Release Phases

Ada tiga fase rilis: 'Current', 'Active Long Term Support (LTS)', and 'Maintenance'.

 * Current - Kode tidak stabil di branch `master` yang sedang dalam pengembangan aktif dan mungkin mengandung bug atau perubahan yang dapat menyebabkan gangguan serta masih mengalami modifikasi signifikan. Direkomendasikan untuk tujuan pembangunan lokal, dan tidak boleh digunakan dalam produksi.
 * Active LTS - Branch versi mayor - cth:(v1.x), dengan fokus pada stabilitas, Fitur baru, perbaikan bug, and keamanan.
 * Maintenance - Branch versi mayor - cth:(v1.x), dengan fokus pada perbaikan bug dan peningkatan keamanan. Terkait fitur baru mungkin ditambahkan jika mendukung migrasi ke rilis selanjutnya.

## Release plan

Rilis baru dibuat dari branch `master` ke versi mayor *Active*. Lihat [Releases Phases](#release-phases) untuk rincian perubahan apa yang diharapkan terjadi pada setiap fase rilis.

## Packages

Core packages can be found in the [`packages`](packages/) directory.

| Directory                           | Distribution                                      |
|:------------------------------------|:--------------------------------------------------|
| [`auth`](packages/auth)             | [codeigniter-xtend/auth]            |
| [`framework`](packages/framework)   | [codeigniter-xtend/framework]       |

## Installation

To start from a working skeleton:

```shell
composer create-project codeigniter-xtend/starter
```

### Standalone packages

The Codeigniter Xtend framework is split into standalone packages which can be used
independently.

```shell
composer require codeigniter-xtend/auth codeigniter-xtend/framework
```