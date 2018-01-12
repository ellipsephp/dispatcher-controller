# Controller resolver

This package provides a factory producing instances of [ellipse/dispatcher](https://github.com/ellipsephp/dispatcher) resolving controller definitions as [Psr-15 request handler](https://github.com/http-interop/http-server-handler) using a [Psr-11 container](http://www.php-fig.org/psr/psr-11/meta/).

**Require** php >= 7.1

**Installation** `composer require ellipse/dispatcher-controller`

**Run tests** `./vendor/bin/kahlan`

- [Getting started](#getting-started)
- [Controller definition](#controller-definition)
- [Controller execution](#controller-execution)
- [Example using auto wiring](#example-using-auto-wiring)

## Getting started

This package provides an `Ellipse\Dispatcher\ControllerResolver` class implementing `Ellipse\DispatcherFactoryInterface` which allows to decorate any other instance implementing this interface.

It takes a container implementing `Psr\Container\ContainerInterface` as first parameter and the factory to decorate as second parameter.

Once decorated, the resulting dispatcher factory can be used to produce instances of `Ellipse\Dispatcher` using controller definitions.

### Controller definition

A controller definition is an array with at least two string elements:

- The first one is the controller fully qualified class name
- The second one is the name of the controller method to execute prepended with `'@'`
- The optional next elements are strings representing names of the request attributes to use as parameters when calling the controller method

For example `[SomeController::class, '@index']` and `[SomeController::class, '@show', 'some_id']` are valid controller definitions. The first one execute the `SomeController` class `->index()` method and the second one execute its `->show($some_id)` method using the value of the request attribute named `'some_id'` as parameter.

This array notation was prefered over a string like `'SomeController@index'` so there is no need to deal with controller namespaces. Also the method name start with a `'@'` because `[SomeController::class, 'index']` is considered as a callable by php, even when the index method is not static!

### Controller execution

Controller instances are retrieved from the container. If you want to build controller instances using auto wiring you can decorate the container with `Ellipse\Container\ReflectionContainer` from the [ellipse/container-reflection](https://github.com/ellipsephp/container-reflection) package before giving it to the `ControllerResolver`.

Then the controller method is executed by using the container to retrieve values for its type hinted parameters. Request attribute values are used for the remaining parameters, in the order they are listed in the controller definition.

Also when the controller method has a parameter type hinted as `Psr\Http\Message\ServerRequestInterface`, the actual Psr-7 request received by the request handler is used. It means when a middleware create a new request (since Psr-7 requests are immutable) the controller method receive this new request.

Finally the controller method must return an instance implementing `Psr\Http\Message\ResponseInterface`. Otherwise an `Ellipse\Dispatcher\Exceptions\ResponseTypeException` is thrown.

```php
<?php

namespace App\Controllers;

use Psr\Http\Message\ServerRequestInterface;

use App\SomeService;
use App\SomeOtherService;

class SomeController
{
    public function __construct(SomeService $service)
    {
        //
    }

    public function index(SomeOtherService $service)
    {
        // return a Psr-7 response
    }

    public function show(SomeOtherService $service, $some_id)
    {
        // return a Psr-7 response
    }

    public function store(ServerRequestInterface $request)
    {
        // return a Psr-7 response
    }
}
```

```php
<?php

namespace App;

use SomePsr11Container;

use Ellipse\DispatcherFactory;
use Ellipse\Dispatcher\ControllerResolver;

use App\Controllers\SomeController;

// Get some incoming Psr-7 request.
$request = some_psr7_request_factory();

// Get some Psr-11 container.
$container = new SomePsr11Container;

// Register the controller in the container.
$container->set(SomeController::class, function ($container) {

    return new SomeController(new SomeService);

});

// Register some services in the container.
$container->set(SomeOtherService::class, function ($container) {

    return new SomeOtherService;

});

// Get a decorated dispatcher factory.
$factory = new ControllerResolver($container, new DispatcherFactory);

// Dispatchers using controller definitions as Psr-15 request handler can now be created.
$dispatcher1 = $factory([SomeController::class, '@index'], [new SomeMiddleware1]);
$dispatcher2 = $factory([SomeController::class, '@show', 'some_id'], [new SomeMiddleware2]);
$dispatcher3 = $factory([SomeController::class, '@store'], [new SomeMiddleware3]);

// Here the SomeController index method is used as final request handler.
$dispatcher1->handle($request);

// Here the SomeController show method is used as final request handler.
// The show method $some_id parameter will receive the request 'some_id' attribute value.
// It is usually added to the request when the route is matched by the router.
$dispatcher2->handle($request);

// Here the SomeController store method is used as final request handler.
// The store method $request parameter will receive the actual Psr-7 request received by the request handler.
// If SomeMiddleware3 update the request then the store method is called with this new request.
$dispatcher3->handle($request);
```

## Example using auto wiring

It can be cumbersome to register every controllers in the container. Here is how to auto wire controller classes using the `Ellipse\Container\ReflectionContainer` class from the [ellipse/container-reflection](https://github.com/ellipsephp/container-reflection) package.

```php
<?php

namespace App;

use SomePsr11Container;

use Ellipse\DispatcherFactory;
use Ellipse\Dispatcher\ControllerResolver;
use Ellipse\Container\ReflectionContainer;

use App\Controllers\SomeController;

// Get some incoming Psr-7 request.
$request = some_psr7_request_factory();

// Get some Psr-11 container.
$container = new SomePsr11Container;

// Register some services in the container.
$container->set(SomeService::class, function ($container) {

    return new SomeService;

});

$container->set(SomeOtherService::class, function ($container) {

    return new SomeOtherService;

});

// Decorate the container with a reflection container.
$container = new ReflectionContainer($container);

// Get a decorated dispatcher factory.
$factory = new ControllerResolver($container, new DispatcherFactory);

// Dispatchers using controller definitions as Psr-15 request handler can now be created.
$dispatcher = $factory([SomeController::class, '@index'], [new SomeMiddleware]);

// Here a new instance of SomeController is built by injecting the defined instance of SomeService.
$dispatcher->handle($request);
```
