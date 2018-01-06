<?php

use function Eloquent\Phony\Kahlan\mock;

use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;

use Psr\Http\Message\ServerRequestInterface;

use Ellipse\Dispatcher\ControllerContainer;

describe('ContainerController', function () {

    beforeall(function () {

        class Dependency1
        {
            private $requuest;
            private $dependency;

            public function __construct(ServerRequestInterface $request, Dependency2 $dependency)
            {
                $this->request = $request;
                $this->dependency = $dependency;
            }
        }

        class Dependency2
        {
            //
        }

        class TestController
        {
            private $dependency;

            public function __construct(Dependency1 $dependency)
            {
                $this->dependency = $dependency;
            }
        }

    });

    beforeEach(function () {

        $this->delegate = mock(ContainerInterface::class);
        $this->request = mock(ServerRequestInterface::class)->get();

        $this->container = new ControllerContainer($this->delegate->get(), $this->request);

    });

    describe('->get()', function () {

        context('when the given id is Psr\\Http\\Message\\ServerRequestInterface', function () {

            it('should return the request instance', function () {

                $test = $this->container->get(ServerRequestInterface::class);

                expect($test)->toBe($this->request);

            });

        });

        context('when the delegate ->get() method does not throw a NotFoundExceptionInterface', function () {

            it('should return the associated instance', function () {

                $instance = new class {};

                $this->delegate->get->with('id')->returns($instance);

                $test = $this->container->get('id');

                expect($test)->toBe($instance);

            });

        });

        context('when the delegate ->get() method throws a NotFoundExceptionInterface', function () {

            beforeEach(function () {

                $exception = mock([Throwable::class, NotFoundExceptionInterface::class]);

                $this->delegate->get->throws($exception);

            });

            context('when the given id is an existing class', function () {

                it('should recursively built it using the request as instance of ServerRequestInterface', function () {

                    $test = $this->container->get(TestController::class);

                    $controller = new TestController(new Dependency1($this->request, new Dependency2));

                    expect($test)->toEqual($controller);

                });

            });

            context('when the given id is not an existing class', function () {

                it('should throw an exception', function () {

                    $test = function () { $this->container->get('id'); };

                    expect($test)->toThrow();

                });

            });

        });

    });

    describe('->has()', function () {

        context('when the given id is Psr\\Http\\Message\\ServerRequestInterface', function () {

            it('should return true', function () {

                $test = $this->container->has(ServerRequestInterface::class);

                expect($test)->toBeTruthy();

            });

        });

        context('when the given id is an existing class name', function () {

            it('should return true', function () {

                $test = $this->container->has(TestController::class);

                expect($test)->toBeTruthy();

            });

        });

        context('when the given id is not an existing class name', function () {

            context('when the delegate ->has() method returns true for the given id', function () {

                it('should return true', function () {

                    $this->delegate->has->with('id')->returns(true);

                    $test = $this->container->has('id');

                    expect($test)->toBeTruthy();

                });

            });

            context('when the delegate ->has() method returns false for the given id', function () {

                it('should return false', function () {

                    $this->delegate->has->with('id')->returns(false);

                    $test = $this->container->has('id');

                    expect($test)->toBeFalsy();

                });

            });

        });

    });

});
