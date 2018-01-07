# Controller resolver

This package provides a factory producing instances of [ellipse/dispatcher](https://github.com/ellipsephp/dispatcher) resolving controller definitions as [Psr-15 request handler](https://github.com/http-interop/http-server-handler) using a [Psr-11 container](http://www.php-fig.org/psr/psr-11/meta/).

**Require** php >= 7.1

**Installation** `composer require ellipse/dispatcher-controller`

**Run tests** `./vendor/bin/kahlan`

- [Getting started](https://github.com/ellipsephp/dispatcher-controller#getting-started)
    - [Controller definition](https://github.com/ellipsephp/dispatcher-controller#controller-definition)
    - [Controller execution](https://github.com/ellipsephp/dispatcher-controller#controller-execution)
    - [Example](https://github.com/ellipsephp/dispatcher-controller#example)

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

The controller instances are built using auto wiring. It means when the controller fully qualified class name is not registered in the container, one instance of this class is built by recursively using auto wiring to build its type hinted constructor parameters.

It allows to use controllers without having to define all the controllers and controller dependencies in the container yet relying on the container when some special construction logic is needed.

In the same way the controller method is called using auto wiring to build its type hinted parameters. Then request attribute values present in the controller definition are used for its non type hinted parameters, in the order they are listed in the definition.

Also when an instance of `Psr\Http\Message\ServerRequestInterface` is needed during auto wiring, the actual Psr-7 request received by the request handler is injected. It means when a middleware create a new request (since Psr-7 requests are immutable) the new request is injected.

Finally the controller method must return an instance implementing `Psr\Http\Message\ResponseInterface`. Otherwise an `Ellipse\Dispatcher\Exceptions\ResponseTypeException` is thrown.

### Example

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

// Register some services in the container.
$container->set(SomeService::class, function ($container) {

    return new SomeService;

});

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

// Here the SomeController show method is used as final request handler. The method
// $some_id parameter will receive the request 'some_id' attribute value. It is usually
// added to the request when the route is matched by the router.
$dispatcher2->handle($request);

// Here the SomeController store method is used as final request handler. The method
// $request parameter will receive the actual Psr-7 request received by the request handler.
$dispatcher3->handle($request);
```
