<?php declare(strict_types=1);

namespace Ellipse\Dispatcher;

use Psr\Container\ContainerInterface;

use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;

use Ellipse\Resolvable\ResolvableCallableFactory;

use Ellipse\Dispatcher\Exceptions\ResponseTypeException;

class Controller
{
    /**
     * The controller string.
     *
     * @var string
     */
    private $str;

    /**
     * Set up a controller with the given controller string.
     *
     * @param string $str
     */
    public function __construct(string $str)
    {
        $this->str = $str;
    }

    /**
     * Return a response from the controller method defined by the controller
     * string. Get the controller class from the given container and execute
     * the controller method using the resolvable callable factory.
     *
     * @param \Psr\Container\ContainerInterface         $container
     * @param \Psr\Http\Message\ServerRequestInterface  $request
     * @return \Psr\Http\Message\ResponseInterface
     * @throws \Ellipse\Dispatcher\Exceptions\ResponseTypeException
     */
    public function response(ContainerInterface $container, ServerRequestInterface $request): ResponseInterface
    {
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
