an awesome set of core improvements for CodeIgniter 3 that makes the development of APIs (and websites in general) more easy!

## Features

* Easy installation via hooks
* routing: prefixes, namespaces, anonymous functions as routes, route groups, named parameters, optional parameters, etc.
* Middleware support
* PHP Debug Bar integration (experimental)

## Installation

#### Step 1: Get with Composer

```
composer require buddywinangun/codeigniter-route
```

#### Step 2: Enable Hooks and Composer autoload

```php
<?php
# application/config/config.php

$config['enable_hooks'] = TRUE;
$config['composer_autoload'] = TRUE;
```

#### Step 3: Connect with CodeIgniter

Set the hooks:

```php
<?php
# application/config/hooks.php

defined('BASEPATH') OR exit('No direct script access allowed');

// (...)

$hook = CodeigniterXtend\Route\Hook::getHooks();
```

Set the routes:

```php
<?php
# application/config/routes.php

defined('BASEPATH') OR exit('No direct script access allowed');

// (...)

$route = CodeigniterXtend\Route\Route::getRoutes();
```