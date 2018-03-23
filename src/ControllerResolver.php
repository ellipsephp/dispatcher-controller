<?php declare(strict_types=1);

namespace Ellipse\Dispatcher;

use Psr\Container\ContainerInterface;

use Psr\Http\Server\RequestHandlerInterface;

use Ellipse\Dispatcher;
use Ellipse\DispatcherFactoryInterface;
use Ellipse\Handlers\ControllerRequestHandler;

class ControllerResolver implements DispatcherFactoryInterface
{
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
     * Set up a controller resolver with the given container and delegate.
     *
     * @param \Psr\Container\ContainerInterface     $container
     * @param \Ellipse\DispatcherFactoryInterface   $delegate
     */
    public function __construct(ContainerInterface $container, DispatcherFactoryInterface $delegate)
    {
        $this->container = $container;
        $this->delegate = $delegate;
    }

    /**
     * Proxy the delegate by wrapping controller definitions into controller
     * request handlers.
     *
     * @param mixed $handler
     * @param array $middleware
     * @return \Ellipse\Dispatcher
     */
    public function __invoke($handler, array $middleware = []): Dispatcher
    {
        // Handler must be an array of at least two elements.
        if (is_array($handler) && count($handler) > 1) {

            // The two elements must be strings.
            if (is_string($handler[0]) && is_string($handler[1])) {

                // The second string must start with an @.
                if ($handler[1][0] === '@') {

                    $class = array_shift($handler);
                    $method = substr(array_shift($handler), 1);
                    $attributes = array_shift($handler) ?? [];


                    $handler = new ControllerRequestHandler($this->container, $class, $method, $attributes);

                }

            }

        }

        return ($this->delegate)($handler, $middleware);
    }
}
