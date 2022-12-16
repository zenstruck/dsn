<?php

/*
 * This file is part of the zenstruck/dsn package.
 *
 * (c) Kevin Bond <kevinbond@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zenstruck\Dsn\Tests\Fixture;

use Symfony\Bundle\FrameworkBundle\FrameworkBundle;
use Symfony\Bundle\FrameworkBundle\Kernel\MicroKernelTrait;
use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\HttpKernel\Kernel as BaseKernel;
use Zenstruck\Dsn\Bridge\Symfony\ZenstruckDsnBundle;
use Zenstruck\Dsn\Parser;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 */
final class Kernel extends BaseKernel
{
    use MicroKernelTrait;

    public function registerBundles(): iterable
    {
        yield new FrameworkBundle();
        yield new ZenstruckDsnBundle();
    }

    protected function configureContainer(ContainerBuilder $c, LoaderInterface $loader): void
    {
        $c->loadFromExtension('framework', [
            'secret' => 'S3CRET',
            'router' => ['utf8' => true],
            'test' => true,
        ]);

        $c->register(Service::class)->setPublic(true)->setAutowired(true);
        $c->register('my_dsn', \Stringable::class)
            ->setFactory([new Reference(Parser::class), 'parse'])
            ->setArguments(['%env(MY_DSN)%'])
            ->setPublic(true)
        ;
    }
}
