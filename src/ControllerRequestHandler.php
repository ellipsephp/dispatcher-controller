<?php declare(strict_types=1);

namespace Ellipse\Dispatcher;

use Psr\Container\ContainerInterface;

use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;

use Interop\Http\Server\RequestHandlerInterface;

use Ellipse\Container\ReflectionContainer;
use Ellipse\Container\OverriddenContainer;
use Ellipse\Resolvable\ResolvableCallableFactory;
use Ellipse\Dispatcher\Exceptions\ResponseTypeException;

class ControllerRequestHandler implements RequestHandlerInterface
{
    /**
     * The container.
     *
     * @var \Psr\Container\ContainerInterface
     */
    private $container;

    /**
     * The controller string.
     *
     * @var string
     */
    private $str;

    /**
     * Set up a controller request handler with the given container and
     * controller string.
     *
     * @param \Psr\Container\ContainerInterface $factory
     * @param string                            $str
     */
    public function __construct(ContainerInterface $container, string $str)
    {
        $this->container = $container;
        $this->str = $str;
    }

    /**
     * Return a response from the controller method defined by the controller
     * string. Use a controller container to get the controller class and
     * execute the controller method using a resolvable callable factory.
     *
     * @param \Psr\Http\Message\ServerRequestInterface  $request
     * @return \Psr\Http\Message\ResponseInterface
     * @throws \Ellipse\Dispatcher\Exceptions\ResponseTypeException
     */
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $container = new ControllerContainer($this->container, $request);

        $factory = new ResolvableCallableFactory;

        $parts = explode(':', $this->str);

        [$class, $method] = explode('@', $parts[0]);

        $attributes = array_filter(preg_split('/\s*,\s*/', $parts[1] ?? ''));

        $placeholders = array_map([$request, 'getAttribute'], $attributes);

        $controller = $container->get($class);

        $response = $factory([$controller, $method])->value($container, $placeholders);

        if ($response instanceof ResponseInterface) {

            return $response;

        }

        throw new ResponseTypeException($response);
    }
}
