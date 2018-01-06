<?php

use function Eloquent\Phony\Kahlan\mock;

use Psr\Container\ContainerInterface;

use Interop\Http\Server\RequestHandlerInterface;

use Ellipse\Dispatcher;
use Ellipse\DispatcherFactoryInterface;
use Ellipse\Dispatcher\ControllerResolver;
use Ellipse\Dispatcher\ControllerRequestHandler;

describe('ControllerResolver', function () {

    beforeEach(function () {

        $this->container = mock(ContainerInterface::class)->get();

        $this->delegate = mock(DispatcherFactoryInterface::class);

    });

    it('should implement DispatcherFactoryInterface', function () {

        $test = new ControllerResolver('', $this->container, $this->delegate->get());

        expect($test)->toBeAnInstanceOf(DispatcherFactoryInterface::class);

    });

    describe('->__invoke()', function () {

        beforeEach(function () {

            $this->dispatcher = mock(Dispatcher::class)->get();

        });

        context('when the given request handler is not a controller string', function () {

            it('should proxy the delegate with the given request handler', function () {

                $resolver = new ControllerResolver('', $this->container, $this->delegate->get());

                $this->delegate->__invoke->with('handler', '~')->returns($this->dispatcher);

                $test = $resolver('handler', []);

                expect($test)->toBe($this->dispatcher);

            });

        });

        context('when the given request handler is a controller string', function () {

            context('when the resolver do not have a controller namespace', function () {

                it('should return a new ControllerRequestHandler using the given controller string', function () {

                    $resolver = new ControllerResolver('', $this->container, $this->delegate->get());

                    $handler = new ControllerRequestHandler($this->container, 'Controller@action:id');

                    $this->delegate->__invoke->with($handler, '~')->returns($this->dispatcher);

                    $test = $resolver('Controller@action:id');

                    expect($test)->toBe($this->dispatcher);

                });

            });

            context('when the resolver has a controller namespace', function () {

                it('should return a new ControllerRequestHandler using the given controller string prepended with the namespace', function () {

                    $resolver = new ControllerResolver('Namespace', $this->container, $this->delegate->get());

                    $handler = new ControllerRequestHandler($this->container, 'Namespace\\Controller@action:id');

                    $this->delegate->__invoke->with($handler, '~')->returns($this->dispatcher);

                    $test = $resolver('Controller@action:id');

                    expect($test)->toBe($this->dispatcher);

                });

            });

        });

        context('when no iterable list of middleware is given', function () {

            it('should proxy the delegate with an empty array', function () {

                $resolver = new ControllerResolver('', $this->container, $this->delegate->get());

                $this->delegate->__invoke->with('~', [])->returns($this->dispatcher);

                $test = $resolver('handler');

                expect($test)->toBe($this->dispatcher);

            });

        });

        context('when an iterable list of middleware is given', function () {

            it('should proxy the delegate with the given iterable list of middleware', function () {

                $test = function ($middleware) {

                    $resolver = new ControllerResolver('', $this->container, $this->delegate->get());

                    $this->delegate->__invoke->with('~', $middleware)->returns($this->dispatcher);

                    $test = $resolver('handler', $middleware);

                    expect($test)->toBe($this->dispatcher);

                };

                $middleware = ['middleware1', 'middleware2'];

                $test($middleware);
                $test(new ArrayIterator($middleware));
                $test(new class ($middleware) implements IteratorAggregate
                {
                    public function __construct($middleware) { $this->middleware = $middleware; }
                    public function getIterator() { return new ArrayIterator($this->middleware); }
                });

            });

        });

    });

});
