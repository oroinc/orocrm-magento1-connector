<?php

namespace Oro\Bundle\MagentoBundle\Async\Topic;

use Oro\Component\MessageQueue\Client\MessagePriority;
use Oro\Component\MessageQueue\Topic\AbstractTopic;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * A topic to sync cart expiration integration
 */
class SyncCartExpirationIntegrationTopic extends AbstractTopic
{
    public static function getName(): string
    {
        return 'oro.magento.sync_cart_expiration_integration';
    }

    public static function getDescription(): string
    {
        return 'Syncs cart expiration integration.';
    }

    public function getDefaultPriority(string $queueName): string
    {
        return MessagePriority::VERY_LOW;
    }

    public function configureMessageBody(OptionsResolver $resolver): void
    {
        $resolver
            ->setRequired('integrationId')
            ->setAllowedTypes('integrationId', 'int');
    }
}
