<?php

namespace Oro\Bundle\MagentoBundle\Migrations\Schema\v1_49;

use Oro\Component\MessageQueue\Topic\AbstractTopic;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * A topic to update relations accounts and contacts
 */
class UpdateRelationsAccountsAndContactsTopic extends AbstractTopic
{
    public const BATCH_NUMBER = 'batch_number';
    public const NAME = 'oro_magento.upgrade_relations_accounts_contacts';

    public static function getName(): string
    {
        return self::NAME;
    }

    public static function getDescription(): string
    {
        return 'Updates relations accounts and contacts. Should be used only during the schema upgrade process.';
    }

    public function configureMessageBody(OptionsResolver $resolver): void
    {
        $resolver
            ->setDefined(self::BATCH_NUMBER)
            ->setAllowedTypes(self::BATCH_NUMBER, 'int');
    }
}
