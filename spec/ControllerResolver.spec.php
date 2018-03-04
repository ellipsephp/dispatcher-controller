<?php

use function Eloquent\Phony\Kahlan\mock;

use Psr\Container\ContainerInterface;

use Psr\Http\Server\RequestHandlerInterface;

use Ellipse\Dispatcher;
use Ellipse\DispatcherFactoryInterface;
use Ellipse\Dispatcher\ControllerResolver;
use Ellipse\Dispatcher\ControllerRequestHandler;

describe('ControllerResolver', function () {

    beforeEach(function () {

        $this->container = mock(ContainerInterface::class)->get();
        $this->delegate = mock(DispatcherFactoryInterface::class);

        $this->resolver = new ControllerResolver($this->container, $this->delegate->get());

    });

    it('should implement DispatcherFactoryInterface', function () {

        expect($this->resolver)->toBeAnInstanceOf(DispatcherFactoryInterface::class);

    });

    describe('->__invoke()', function () {

        beforeEach(function () {

            $this->dispatcher = mock(Dispatcher::class)->get();

        });

        context('when the given request handler is not a controller definition', function () {

            it('should proxy the delegate with the given request handler', function () {

                $test = function ($handler) {

                    $this->delegate->__invoke->with($handler, '~')->returns($this->dispatcher);

                    $test = ($this->resolver)($handler, []);

                    expect($test)->toBe($this->dispatcher);

                };

                $test('Controller'); // Not an array
                $test([]); // Empty array
                $test(['Controller']); // Array of one value
                $test([1, 1]); // Both values are not strings
                $test(['Controller', 1]); // Second value is not a string
                $test([1, 'action']); // First value is not a string
                $test(['Controller', 'action']); // Second string does not start with @

            });

        });

        context('when the given request handler is a controller definition', function () {

            context('when the controller definition does not have attributes', function () {

                it('should return a new ControllerRequestHandler using the defined controller, action and an empty array of attributes', function () {

                    $handler = new ControllerRequestHandler($this->container, 'Controller', 'action', []);

                    $this->delegate->__invoke->with($handler, '~')->returns($this->dispatcher);

                    $test = ($this->resolver)(['Controller', '@action']);

                    expect($test)->toBe($this->dispatcher);

                });

            });

            context('when the controller definition has attributes', function () {

                it('should return a new ControllerRequestHandler using the defined controller, action and attributes', function () {

                    $handler = new ControllerRequestHandler($this->container, 'Controller', 'action', ['id']);

                    $this->delegate->__invoke->with($handler, '~')->returns($this->dispatcher);

                    $test = ($this->resolver)(['Controller', '@action', 'id']);

                    expect($test)->toBe($this->dispatcher);

                });

            });

        });

        context('when no middleware queue is given', function () {

            it('should proxy the delegate with an empty array', function () {

                $this->delegate->__invoke->with('~', [])->returns($this->dispatcher);

                $test = ($this->resolver)('handler');

                expect($test)->toBe($this->dispatcher);

            });

        });

        context('when an middleware queue is given', function () {

            it('should proxy the delegate with the given middleware queue', function () {

                $this->delegate->__invoke->with('~', ['middleware'])->returns($this->dispatcher);

                $test = ($this->resolver)('handler', ['middleware']);

                expect($test)->toBe($this->dispatcher);

            });

        });

    });

});
