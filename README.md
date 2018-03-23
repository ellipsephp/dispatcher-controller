# Controller resolver

This package provides a factory decorator for objects implementing `Ellipse\DispatcherFactoryInterface` from [ellipse/dispatcher](https://github.com/ellipsephp/dispatcher) package. It allows to produce instances of `Ellipse\Dispatcher` using [controller definitions](#controller-definitions).

**Require** php >= 7.0

**Installation** `composer require ellipse/dispatcher-controller`

**Run tests** `./vendor/bin/kahlan`

- [Create a dispatcher factory resolving controller definitions](#create-a-dispatcher-factory-resolving-controller-definitions)
- [Controller definitions](#controller-definitions)

## Create a dispatcher factory resolving controller definitions

This package provides an `Ellipse\Dispatcher\ControllerResolver` class implementing `Ellipse\DispatcherFactoryInterface` which allows to decorate any other object implementing this interface.

It takes a container implementing `Psr\Container\ContainerInterface` as first parameter and the factory to decorate as second parameter.

Once decorated, the resulting dispatcher factory can be used to produce instances of `Ellipse\Dispatcher` by resolving [controller definitions](#controller-definitions) as `Ellipse\Handlers\ControllerRequestHandler` instances from the [ellipse/handlers-controller](https://github.com/ellipsephp/handlers-controller) package.

```php
<?php

namespace App;

use SomePsr11Container;

use Ellipse\DispatcherFactory;
use Ellipse\Dispatcher\ControllerResolver;

// Get some Psr-11 container.
$container = new SomePsr11Container;

// Decorate a DispatcherFactoryInterface implementation with a ControllerResolver.
$factory = new ControllerResolver($container, new DispatcherFactory);
```

## Controller definitions

An instance of `ControllerRequestHandler` needs the container entry id of an object used as controller, a method name and an optional array of request attribute names. A controller definition defines which controller class, method name and request attributes should be used by the `ControllerRequestHandler`. It is an array with at least two string elements:

- The first one is the controller fully qualified class name
- The second one is the name of the controller method to execute prepended with `'@'`
- The optional next elements are strings representing names of the request attributes to use as parameters when calling the controller method

For example `[SomeController::class, '@index']` and `[SomeController::class, '@show', 'some_id']` are valid controller definitions. The first one execute the `SomeController` class `->index()` method and the second one execute its `->show($some_id)` method using the value of the request attribute named `'some_id'` as parameter.

This array notation was prefered over a string like `'SomeController@index'` so there is no need to deal with controller namespaces. Also the method name start with a `'@'` because `[SomeController::class, 'index']` is considered as a callable by php, even when the index method is not static!

`ControllerRequestHandler` logic is described on the [ellipse/handlers-controller](https://github.com/ellipsephp/handlers-controller#using-controllers-as-request-handlers) documentation page.

```php
<?php

namespace App;

use SomePsr11Container;

use Ellipse\DispatcherFactory;
use Ellipse\Dispatcher\ControllerResolver;

use App\Controllers\SomeController;

// Get some Psr-11 container.
$container = new SomePsr11Container;

// Decorate a DispatcherFactoryInterface implementation with a ControllerResolver.
$factory = new ControllerResolver($container, new DispatcherFactory);

// Dispatchers using controller definitions as Psr-15 request handler can now be created.
$dispatcher1 = $factory([SomeController::class, '@index'], [new SomeMiddleware]);
$dispatcher2 = $factory([SomeController::class, '@show', 'some_id'], [new SomeMiddleware]);
$dispatcher3 = $factory([SomeController::class, '@store'], [new SomeMiddleware]);
```
