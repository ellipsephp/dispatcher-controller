<?php declare(strict_types=1);

namespace Ellipse\Dispatcher;

use Interop\Http\Server\RequestHandlerInterface;

use Ellipse\Dispatcher;
use Ellipse\DispatcherFactoryInterface;
use Ellipse\Dispatcher\ContainerFactory;

class ControllerResolver implements DispatcherFactoryInterface
{
    /**
     * The controller namespace.
     *
     * @var string
    */
    private $namespace;

    /**
     * The container factory.
     *
     * @var \Ellipse\Dispatcher\ContainerFactory
     */
    private $factory;

    /**
     * The delegate.
     *
     * @var \Ellipse\DispatcherFactoryInterface
     */
    private $delegate;

    /**
     * Set up a controller resolver with the given controller namespace, factory
     * and delegate.
     *
     * @param string                                $namespace
     * @param callable                              $factory
     * @param \Ellipse\DispatcherFactoryInterface   $delegate
     */
    public function __construct(string $namespace, callable $factory, DispatcherFactoryInterface $delegate)
    {
        $this->namespace = $namespace;
        $this->factory = new ContainerFactory($factory);
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

            $handler = new ControllerRequestHandler($this->factory, new Controller($str));

        }

        return ($this->delegate)($handler, $middleware);
    }
}
