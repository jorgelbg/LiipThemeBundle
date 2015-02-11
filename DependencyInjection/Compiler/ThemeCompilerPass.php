<?php

/*
 * This file is part of the Liip/ThemeBundle
 *
 * (c) Liip AG
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Liip\ThemeBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;

class ThemeCompilerPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container)
    {
        $container->setAlias('templating.locator', 'liip_theme.templating_locator');

        $container->setAlias('templating.cache_warmer.template_paths', 'liip_theme.templating.cache_warmer.template_paths');

        if (true === $container->hasDefinition('twig')) {
            $twigLoader = $container->findDefinition('twig.loader');
            $aliasedTo = $this->resolveAlias('twig.loader', $container);
            if ($aliasedTo == 'twig.loader.chain') {
                $methodCalls = $twigLoader->getMethodCalls();
                foreach($methodCalls as $index => $methodCall) {
                    if ($methodCall[0] == 'addLoader' && (string) $methodCall[1][0] == 'twig.loader.filesystem') {
                        $methodCalls[$index] = array($methodCall[0], array(new Reference('liip_theme.twig.loader.filesystem')));
                    }
                }
                $twigLoader->setMethodCalls($methodCalls);
            } elseif ($aliasedTo == 'twig.loader.filesystem') {
                $container->setAlias('twig.loader.filesystem', 'liip_theme.twig.loader.filesystem');
            }
        }

        if (!$container->getParameter('liip_theme.cache_warming')) {
            $container->getDefinition('liip_theme.templating.cache_warmer.template_paths')
                ->replaceArgument(2, null);
        }
    }

    public function resolveContainerAlias($id, ContainerBuilder $container) {
        while($container->hasAlias($id)) {
            $id = $container->getAlias($id);
        }

        return $id;
    }

}
