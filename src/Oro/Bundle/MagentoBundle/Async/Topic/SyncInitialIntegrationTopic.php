<?php

namespace Oro\Bundle\MagentoBundle\Async\Topic;

use Oro\Component\MessageQueue\Client\MessagePriority;
use Oro\Component\MessageQueue\Topic\AbstractTopic;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * A topic to sync initial integration
 */
class SyncInitialIntegrationTopic extends AbstractTopic
{
    public static function getName(): string
    {
        return 'oro.magento.sync_initial_integration';
    }

    public static function getDescription(): string
    {
        return 'Syncs initial integration.';
    }

    public function getDefaultPriority(string $queueName): string
    {
        return MessagePriority::VERY_LOW;
    }

    public function configureMessageBody(OptionsResolver $resolver): void
    {
        $resolver
            ->setRequired('integration_id')
            ->setAllowedTypes('integration_id', 'int');

        $resolver
            ->setDefined('connector')
            ->setDefault('connector', null)
            ->setAllowedTypes('connector', ['string', 'null']);

        $resolver
            ->setDefined('connector_parameters')
            ->setDefault('connector_parameters', [])
            ->setAllowedTypes('connector_parameters', 'array');
    }
}
