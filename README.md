# Slim Whoops

Slim Framework 3 error handler built on top of Whoops Error Handler

## Installing

Use Composer to install Whoops into your project:
```
composer require dopesong/slim-whoops
```

## Requirements
- PHP >=5.5.0
- Whoops ~1.1

## Usage With Slim 3

```php
use Dopesong\Slim\Error\Whoops as WhoopsError;

include "vendor/autoload.php";

$app = new Slim\App();
$container = $app->getContainer();

$container['errorHandler'] = function($c) {
    return new WhoopsError($c->get('settings')['displayErrorDetails']);
};

$app->run();
```