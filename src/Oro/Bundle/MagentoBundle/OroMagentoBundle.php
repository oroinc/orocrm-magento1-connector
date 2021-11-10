<?php

namespace Oro\Bundle\MagentoBundle;

use Oro\Bundle\MagentoBundle\DependencyInjection\Compiler\ExcludeDictionaryEntitiesFromRestApiPass;
use Oro\Component\DependencyInjection\Compiler\PriorityTaggedLocatorCompilerPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

/**
 * The MagentoBundle bundle class.
 */
class OroMagentoBundle extends Bundle
{
    /**
     * {@inheritdoc}
     */
    public function build(ContainerBuilder $container)
    {
        $container->addCompilerPass(new PriorityTaggedLocatorCompilerPass(
            'oro_magento.converter.rest.response_converter_manager',
            'oro_magento.rest_response.converter',
            'type'
        ));
        $container->addCompilerPass(new ExcludeDictionaryEntitiesFromRestApiPass());
    }
}
