<?php

/*
 * This file is part of the zenstruck/dsn package.
 *
 * (c) Kevin Bond <kevinbond@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zenstruck\Dsn\Bridge\Symfony;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\ExtensionInterface;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\HttpKernel\Bundle\Bundle;
use Zenstruck\Dsn\Parser;
use Zenstruck\Dsn\Parser\CacheParser;
use Zenstruck\Dsn\Parser\ChainParser;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 */
final class ZenstruckDsnBundle extends Bundle
{
    public function build(ContainerBuilder $container): void
    {
        $container->register('zenstruck_dsn.chain_parser', ChainParser::class);
        $container->register('zenstruck_dsn.cache_parser', CacheParser::class)
            ->setArguments([new Reference('zenstruck_dsn.chain_parser'), new Reference('cache.system')])
        ;
        $container->setAlias(Parser::class, 'zenstruck_dsn.cache_parser');
    }

    public function getContainerExtension(): ?ExtensionInterface
    {
        return null;
    }
}
