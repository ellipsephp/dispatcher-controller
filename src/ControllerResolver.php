<?php declare(strict_types=1);

namespace Ellipse\Dispatcher;

use Psr\Container\ContainerInterface;

use Interop\Http\Server\RequestHandlerInterface;

use Ellipse\Dispatcher;
use Ellipse\DispatcherFactoryInterface;

class ControllerResolver implements DispatcherFactoryInterface
{
    /**
     * The controller namespace.
     *
     * @var string
    */
    private $namespace;

    /**
     * The container.
     *
     * @var \Psr\Container\ContainerInterface
     */
    private $container;

    /**
     * The delegate.
     *
     * @var \Ellipse\DispatcherFactoryInterface
     */
    private $delegate;

    /**
     * Set up a controller resolver with the given controller namespace,
     * container and delegate.
     *
     * @param string                                $namespace
     * @param \Psr\Container\ContainerInterface     $container
     * @param \Ellipse\DispatcherFactoryInterface   $delegate
     */
    public function __construct(string $namespace, ContainerInterface $container, DispatcherFactoryInterface $delegate)
    {
        $this->namespace = $namespace;
        $this->container = $container;
        $this->delegate = $delegate;
    }

    /**
     * Proxy the delegate by wrapping controller strings into controller request
     * handlers.
     *
     * @param mixed     $handler
     * @param iterable  $middleware
     * @return \Ellipse\Dispatcher
     */
    public function __invoke($handler, iterable $middleware = []): Dispatcher
    {
        if (is_string($handler) && strpos($handler, '@') !== false) {

            $str = $this->namespace == '' ? $handler : $this->namespace . '\\' . $handler;

            $handler = new ControllerRequestHandler($this->container, $str);

        }

        return ($this->delegate)($handler, $middleware);
    }
}
