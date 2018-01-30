<?php declare(strict_types=1);

namespace Ellipse\Dispatcher;

use Psr\Container\ContainerInterface;

use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Server\RequestHandlerInterface;

use Ellipse\Container\ReflectionContainer;
use Ellipse\Container\OverriddenContainer;
use Ellipse\Resolvable\DefaultResolvableCallableFactory;

class ControllerRequestHandler implements RequestHandlerInterface
{
    /**
     * The container.
     *
     * @var \Psr\Container\ContainerInterface
     */
    private $container;

    /**
     * The resolvable callable factory.
     *
     * @var \Ellipse\Resolvable\DefaultResolvableCallableFactory
     */
    private $factory;

    /**
     * The controller class name.
     *
     * @var string
     */
    private $class;

    /**
     * The controller method.
     *
     * @var string
     */
    private $method;

    /**
     * The request attributes to inject in the method.
     *
     * @var array
     */
    private $attributes;

    /**
     * Set up a controller request handler with the given container, class name,
     * method and attributes.
     *
     * @param \Psr\Container\ContainerInterface $factory
     * @param string                            $class
     * @param string                            $method
     * @param array                             $attributes
     */
    public function __construct(ContainerInterface $container, string $class, string $method, array $attributes = [])
    {
        $this->container = $container;
        $this->factory = new DefaultResolvableCallableFactory;
        $this->class = $class;
        $this->method = $method;
        $this->attributes = $attributes;
    }

    /**
     * Return a response from the controller method. Use a controller container
     * using the given request to get the controller class and execute the
     * controller method using the resolvable callable factory.
     *
     * @param \Psr\Http\Message\ServerRequestInterface  $request
     * @return \Psr\Http\Message\ResponseInterface
     */
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $container = new ControllerContainer($this->container, $request);

        $placeholders = array_map([$request, 'getAttribute'], $this->attributes);

        $controller = $container->get($this->class);

        $action = [$controller, $this->method];

        return ($this->factory)($action)->value($container, $placeholders);
    }
}
