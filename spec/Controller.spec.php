<?php

use function Eloquent\Phony\Kahlan\mock;

use Psr\Container\ContainerInterface;

use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;

use Ellipse\Resolvable\ResolvableCallable;
use Ellipse\Resolvable\ResolvableCallableFactory;

use Ellipse\Dispatcher\Controller;
use Ellipse\Dispatcher\Exceptions\ResponseTypeException;

describe('Controller', function () {

    describe('->response()', function () {

        beforeEach(function () {

            $factory = mock(ResolvableCallableFactory::class);

            allow(ResolvableCallableFactory::class)->toBe($factory->get());

            $this->container = mock(ContainerInterface::class);

            $this->request = mock(ServerRequestInterface::class);
            $this->response = mock(ResponseInterface::class)->get();

            $this->resolvable = mock(ResolvableCallable::class);

            $controller = mock(['action' => function () {}])->get();

            $this->container->get->with('Controller')->returns($controller);

            $factory->__invoke->with([$controller, 'action'])->returns($this->resolvable);

        });

        context('when the controller returns an implementation of ResponseInterface', function () {

            context('when the controller string do not have attribute values', function () {

                it('should resolve the controller action using the attribute values as placeholders', function () {

                    $controller = new Controller('Controller@action');

                    $this->resolvable->value->with($this->container, [])->returns($this->response);

                    $test = $controller->response($this->container->get(), $this->request->get());

                    expect($test)->toBe($this->response);

                });

            });

            context('when the controller string has attribute values', function () {

                it('should resolve the controller action using the attribute values as placeholders', function () {

                    $controller = new Controller('Controller@action:a1,a2');

                    $this->request->getAttribute->with('a1')->returns('v1');
                    $this->request->getAttribute->with('a2')->returns('v2');

                    $this->resolvable->value->with($this->container, ['v1', 'v2'])->returns($this->response);

                    $test = $controller->response($this->container->get(), $this->request->get());

                    expect($test)->toBe($this->response);

                });

            });

        });

        context('when the controller does not return an implementation of ResponseInterface', function () {

            it('should throw a ResponseTypeException', function () {

                $controller = new Controller('Controller@action');

                $this->resolvable->value->with($this->container, [])->returns('response');

                $test = function () use ($controller) {

                    $controller->response($this->container->get(), $this->request->get());

                };

                $exception = new ResponseTypeException('response');

                expect($test)->toThrow($exception);

            });

        });

    });

});
