<?php

use function Eloquent\Phony\Kahlan\mock;

use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;

use Psr\Http\Message\ServerRequestInterface;

use Ellipse\Dispatcher\ControllerContainer;

describe('ContainerController', function () {

    beforeEach(function () {

        $this->delegate = mock(ContainerInterface::class);
        $this->request = mock(ServerRequestInterface::class)->get();

        $this->container = new ControllerContainer($this->delegate->get(), $this->request);

    });

    describe('->get()', function () {

        context('when the given id is Psr\\Http\\Message\\ServerRequestInterface', function () {

            it('should return the request instance', function () {

                $test = $this->container->get(ServerRequestInterface::class);

                expect($test)->toBe($this->request);

            });

        });

        context('when the given id is not Psr\\Http\\Message\\ServerRequestInterface', function () {

            it('should proxy the delegate', function () {

                $instance = new class {};

                $this->delegate->get->with('id')->returns($instance);

                $test = $this->container->get('id');

                expect($test)->toBe($instance);

            });

        });

    });

    describe('->has()', function () {

        context('when the given id is Psr\\Http\\Message\\ServerRequestInterface', function () {

            it('should return true', function () {

                $test = $this->container->has(ServerRequestInterface::class);

                expect($test)->toBeTruthy();

            });

        });

        context('when the given id is not Psr\\Http\\Message\\ServerRequestInterface', function () {

            context('when the delegate ->has() method returns true for the given id', function () {

                it('should return true', function () {

                    $this->delegate->has->with('id')->returns(true);

                    $test = $this->container->has('id');

                    expect($test)->toBeTruthy();

                });

            });

            context('when the delegate ->has() method returns false for the given id', function () {

                it('should return false', function () {

                    $this->delegate->has->with('id')->returns(false);

                    $test = $this->container->has('id');

                    expect($test)->toBeFalsy();

                });

            });

        });

    });

});
