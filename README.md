# Slim Whoops

Slim Framework 3 error handler built on top of Whoops Error Handler

## Installing

Use Composer to install Whoops into your project:
```
composer require dopesong/slim-whoops
```

## Requirements
- PHP >=5.6.0
- Whoops ^2.0

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