This repository is a monorepo containing the entirety of the web framework.
Codeigniter Xtend can be used as a full stack framework or as standalone packages which
can be used independently.

## Codeigniter Xtend packages

Core packages can be found in the [`packages`](packages/) directory.

| Directory                           | Distribution                                      |
|:------------------------------------|:--------------------------------------------------|
| [`auth`](packages/auth)             | [buddywinangun/codeigniter-xtend-auth]            |
| [`framework`](packages/framework)   | [buddywinangun/codeigniter-xtend-framework]       |

## Installation

The preferred (and only supported) method is with Composer:

```shell
composer require buddywinangun/codeigniter-xtend
```

To start from a working skeleton:

```shell
composer create-project buddywinangun/codeigniter-xtend-appstarter
```

### Standalone packages

The Codeigniter Xtend framework is split into standalone packages which can be used
independently.

```shell
composer require buddywinangun/codeigniter-xtend-auth buddywinangun/codeigniter-xtend-framework
```