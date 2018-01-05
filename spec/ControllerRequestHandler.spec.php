<?php

use function Eloquent\Phony\Kahlan\mock;

use Psr\Container\ContainerInterface;

use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;

use Interop\Http\Server\RequestHandlerInterface;

use Ellipse\Dispatcher\Controller;
use Ellipse\Dispatcher\ControllerRequestHandler;
use Ellipse\Dispatcher\ContainerFactory;

describe('ControllerRequestHandler', function () {

    beforeEach(function () {

        $this->factory = mock(ContainerFactory::class);
        $this->controller = mock(Controller::class);

        $this->handler = new ControllerRequestHandler($this->factory->get(), $this->controller->get());

    });

    it('should implement RequestHandlerInterface', function () {

        expect($this->handler)->toBeAnInstanceOf(RequestHandlerInterface::class);

    });

    describe('->handle()', function () {

        it('should proxy the controller ->response() method with the container and the given request', function () {

            $container = mock(ContainerInterface::class)->get();

            $request = mock(ServerRequestInterface::class)->get();
            $response = mock(ResponseInterface::class)->get();

            $this->factory->__invoke->with($request)->returns($container);

            $this->controller->response->with($container, $request)->returns($response);

            $test = $this->handler->handle($request);

            expect($test)->toBe($response);

        });

    });

});
