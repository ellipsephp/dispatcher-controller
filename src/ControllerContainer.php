<?php declare(strict_types=1);

namespace Ellipse\Dispatcher;

use Psr\Container\ContainerInterface;

use Psr\Http\Message\ServerRequestInterface;

use Ellipse\Container\ReflectionContainer;
use Ellipse\Container\OverriddenContainer;

class ControllerContainer implements ContainerInterface
{
    /**
     * The delegate.
     *
     * @var \Ellipse\Container\ReflectionContainer
     */
    private $delegate;

    /**
     * Set up a controller container with the given container and request.
     *
     * @param \Psr\Container\ContainerInterface         $container
     * @param \Psr\Http\Message\ServerRequestInterface  $request
     */
    public function __construct(ContainerInterface $container, ServerRequestInterface $request)
    {
        $this->delegate = new ReflectionContainer(
            new OverriddenContainer($container, [
                ServerRequestInterface::class => $request,
            ])
        );
    }

    /**
     * @inheritdoc
     */
    public function get($id)
    {
        return $this->delegate->get($id);
    }

    /**
     * @inheritdoc
     */
    public function has($id)
    {
        return $this->delegate->has($id);
    }
}
