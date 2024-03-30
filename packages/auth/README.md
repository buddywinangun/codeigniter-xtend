## Features

* Easy installation via hooks
* Authentication library with SimpleAuth template

## Installation

#### Step 1: Get with Composer

```
composer require buddywinangun/codeigniter-auth
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

$hook = [];
$hook = CodeigniterXtend\Auth\Hook::getHooks($hook);
```

#### Step 4: Install the database

To install the database, run the following from the command line:

```php
php index.php migrate
```

#### Step 5: Define the routes

In your web.php route file, add the following line:

```php
// Sets the default routing
\CodeigniterXtend\Auth\SimpleAuth\Routes::getRoutes();
```