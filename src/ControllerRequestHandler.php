<?php declare(strict_types=1);

namespace Ellipse\Dispatcher;

use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;

use Interop\Http\Server\RequestHandlerInterface;

use Ellipse\Dispatcher\ContainerFactory;

class ControllerRequestHandler implements RequestHandlerInterface
{
    /**
     * The container factory.
     *
     * @var \Ellipse\Dispatcher\ContainerFactory
     */
    private $factory;

    /**
     * The controller to use to produce a response.
     *
     * @var \Ellipse\Dispatcher\Controller
     */
    private $controller;

    /**
     * Set up a controller request handler with the given factory and
     * controller.
     *
     * @param \Ellipse\Dispatcher\ContainerFactory  $factory
     * @param \Ellipse\Dispatcher\Controller        $controller
     */
    public function __construct(ContainerFactory $factory, Controller $controller)
    {
        $this->factory = $factory;
        $this->controller = $controller;
    }

    /**
     * Use the controller to produce a response from the given request with the
     * container produced by the container factory.
     *
     * @param \Psr\Http\Message\ServerRequestInterface  $request
     * @return \Psr\Http\Message\ResponseInterface
     */
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $container = ($this->factory)($request);

        return $this->controller->response($container, $request);
    }
}
