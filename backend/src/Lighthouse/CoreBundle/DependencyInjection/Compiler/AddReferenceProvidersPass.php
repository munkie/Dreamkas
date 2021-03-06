<?php

namespace Lighthouse\CoreBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Exception\ParameterNotFoundException;
use Symfony\Component\DependencyInjection\Exception\ServiceNotFoundException;
use Symfony\Component\DependencyInjection\Reference;

class AddReferenceProvidersPass implements CompilerPassInterface
{
    /**
     * @param ContainerBuilder $container
     * @throws \Symfony\Component\DependencyInjection\Exception\ParameterNotFoundException
     * @throws \Symfony\Component\DependencyInjection\Exception\ServiceNotFoundException
     */
    public function process(ContainerBuilder $container)
    {
        if (false === $container->hasDefinition('lighthouse.core.mongodb.reference.manager')) {
            throw new ServiceNotFoundException('lighthouse.core.mongodb.reference.manager');
        }

        $definition = $container->getDefinition('lighthouse.core.mongodb.reference.manager');

        $providers = array();
        foreach ($container->findTaggedServiceIds('reference.provider') as $id => $tagAttributes) {
            if (!isset($tagAttributes[0]['alias'])) {
                throw new ParameterNotFoundException('alias');
            }
            $providers[$tagAttributes[0]['alias']] = new Reference($id);
        }
        $definition->setArguments(array($providers));
    }
}
