<?php

use function Eloquent\Phony\Kahlan\mock;

use Psr\Container\ContainerInterface;

use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;

use Interop\Http\Server\RequestHandlerInterface;

use Ellipse\Resolvable\ResolvableCallable;
use Ellipse\Resolvable\ResolvableCallableFactory;

use Ellipse\Dispatcher\ControllerContainer;
use Ellipse\Dispatcher\ControllerRequestHandler;
use Ellipse\Dispatcher\Exceptions\ResponseTypeException;

describe('ControllerRequestHandler', function () {

    beforeEach(function () {

        $this->container = mock(ContainerInterface::class);

    });

    it('should implement RequestHandlerInterface', function () {

        $test = new ControllerRequestHandler($this->container->get(), 'Controller@action');

        expect($test)->toBeAnInstanceOf(RequestHandlerInterface::class);

    });

    describe('->handle()', function () {

        beforeEach(function () {

            $factory = mock(ResolvableCallableFactory::class);

            allow(ResolvableCallableFactory::class)->toBe($factory->get());

            $this->request = mock(ServerRequestInterface::class);
            $this->response = mock(ResponseInterface::class)->get();

            $this->reflection = new ControllerContainer($this->container->get(), $this->request->get());

            $controller = mock(['action' => function () {}])->get();

            $this->container->get->with('Controller')->returns($controller);

            $this->resolvable = mock(ResolvableCallable::class);

            $factory->__invoke->with([$controller, 'action'])->returns($this->resolvable);

        });

        context('when the controller returns an implementation of ResponseInterface', function () {

            context('when the controller string do not have attribute values', function () {

                it('should resolve the controller action using an empty array placeholders', function () {

                    $handler = new ControllerRequestHandler($this->container->get(), 'Controller@action');

                    $this->resolvable->value->with($this->reflection, [])->returns($this->response);

                    $test = $handler->handle($this->request->get());

                    expect($test)->toBe($this->response);

                });

            });

            context('when the controller string has attribute values', function () {

                it('should resolve the controller action using the attribute values as placeholders', function () {

                    $handler = new ControllerRequestHandler($this->container->get(), 'Controller@action:a1,a2');

                    $this->request->getAttribute->with('a1')->returns('v1');
                    $this->request->getAttribute->with('a2')->returns('v2');

                    $this->resolvable->value->with($this->reflection, ['v1', 'v2'])->returns($this->response);

                    $test = $handler->handle($this->request->get());

                    expect($test)->toBe($this->response);

                });

            });

        });

        context('when the controller does not return an implementation of ResponseInterface', function () {

            it('should throw a ResponseTypeException', function () {

                $handler = new ControllerRequestHandler($this->container->get(), 'Controller@action');

                $this->resolvable->value->with($this->reflection, [])->returns('response');

                $test = function () use ($handler) {

                    $handler->handle($this->request->get());

                };

                $exception = new ResponseTypeException('response');

                expect($test)->toThrow($exception);

            });

        });

    });

});
