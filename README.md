# Slim Whoops

[![Latest Stable Version](https://poser.pugx.org/dopesong/slim-whoops/v/stable)](https://packagist.org/packages/dopesong/slim-whoops)
[![Total Downloads](https://poser.pugx.org/dopesong/slim-whoops/downloads)](https://packagist.org/packages/dopesong/slim-whoops)
[![Latest Unstable Version](https://poser.pugx.org/dopesong/slim-whoops/v/unstable)](https://packagist.org/packages/dopesong/slim-whoops)
[![License](https://poser.pugx.org/dopesong/slim-whoops/license)](https://packagist.org/packages/dopesong/slim-whoops)

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

$container['phpErrorHandler'] = $container['errorHandler'] = function($c) {
    return new WhoopsError($c->get('settings')['displayErrorDetails']);
};

$app->run();
```

## Additional handlers
 
Custom handlers can be added to execute additional tasks.
For example, you might want to log the error like so:

```php
include "vendor/autoload.php";

use Whoops\Handler\Handler;
use Dopesong\Slim\Error\Whoops as WhoopsError;

$app = new Slim\App();
$container = $app->getContainer();

$container['phpErrorHandler'] = $container['errorHandler'] = function ($container) {
    $logger = $container['logger'];
    $whoopsHandler = new WhoopsError();

    $whoopsHandler->pushHandler(
        function ($exception) use ($logger) {
            /** @var \Exception $exception */
            $logger->error($exception->getMessage(), ['exception' => $exception]);
            return Handler::DONE;
        }
    );

    return $whoopsHandler;
};
```

